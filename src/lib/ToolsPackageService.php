<?php

declare(strict_types=1);

/**
 * ToolsPackageService
 *
 * Secure ZIP export and import manager for AI Custom Tools.
 * Implements Zip-Slip prevention, Zip-Bomb deterrence, SSRF protocol whitelisting,
 * RBAC validation, tenant isolation, and atomic transactional commits.
 */
class ToolsPackageService
{
    private const MAX_ZIP_SIZE = 10485760; // 10MB
    private const MAX_UNCOMPRESSED_TOTAL = 26214400; // 25MB
    private const MAX_ENTRIES_COUNT = 150;
    private const MAX_SINGLE_ENTRY_SIZE = 5242880; // 5MB

    private const ALLOWED_CATEGORIES = ['rest_api', 'shared_env_api', 'webhook', 'builtin'];
    private const ALLOWED_ROLES = ['guest', 'customer', 'admin'];

    /**
     * Export selected user tools to a secure ZIP archive.
     *
     * @param int $userId Authenticated user ID
     * @param array<int> $toolIds List of tool IDs to export (empty means all)
     * @return array{path: string, filename: string, count: int}
     * @throws RuntimeException On archive creation failure or no tools found
     */
    public static function exportToolsZip(int $userId, array $toolIds = []): array
    {
        $pdo = Database::pdo();

        if (!empty($toolIds)) {
            $inClause = implode(',', array_fill(0, count($toolIds), '?'));
            $params = array_merge([$userId], array_map('intval', $toolIds));
            $stmt = $pdo->prepare("SELECT * FROM ai_custom_tools WHERE user_id = ? AND id IN ($inClause) ORDER BY id ASC");
            $stmt->execute($params);
        } else {
            $stmt = $pdo->prepare('SELECT * FROM ai_custom_tools WHERE user_id = ? ORDER BY id ASC');
            $stmt->execute([$userId]);
        }

        $tools = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$tools) {
            throw new RuntimeException('No matching tools found to export.');
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'tool_pkg_exp_');
        if ($tempFile === false) {
            throw new RuntimeException('Could not initialize temporary storage for export.');
        }

        $zip = new ToolsPackageZipWriter($tempFile);

        $manifestTools = [];
        $exportTimestamp = gmdate('Y-m-d\TH:i:s\Z');

        foreach ($tools as $tool) {
            $slug = preg_replace('/[^a-z0-9_]/', '_', strtolower((string) ($tool['tool_slug'] ?? 'tool')));
            $cleanName = trim((string) ($tool['display_name'] ?? $slug));
            $category = (string) ($tool['category'] ?? 'rest_api');

            $paramSchema = !empty($tool['parameters_schema']) ? json_decode((string) $tool['parameters_schema'], true) : [];
            $execConfig = !empty($tool['execution_config']) ? json_decode((string) $tool['execution_config'], true) : [];
            $roles = !empty($tool['rbac_roles']) ? json_decode((string) $tool['rbac_roles'], true) : ['guest', 'customer', 'admin'];

            $toolPayload = [
                'schema_version' => '1.0',
                'tool_slug' => $slug,
                'display_name' => $cleanName,
                'category' => $category,
                'description' => (string) ($tool['description'] ?? ''),
                'parameters_schema' => $paramSchema ?: ['type' => 'OBJECT', 'properties' => (object)[], 'required' => []],
                'execution_config' => $execConfig ?: [],
                'rbac_roles' => array_values(array_intersect($roles, self::ALLOWED_ROLES)),
                'is_active' => (int) ($tool['is_active'] ?? 1),
                'exported_at' => $exportTimestamp,
            ];

            $entryJson = json_encode($toolPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $zip->addFromString("tools/{$slug}.json", $entryJson);

            $manifestTools[] = [
                'slug' => $slug,
                'display_name' => $cleanName,
                'category' => $category,
                'file' => "tools/{$slug}.json",
            ];
        }

        $manifest = [
            'format' => 'whatsapp_api_tools_package',
            'version' => '1.0',
            'generator' => 'WhatsApp Gateway Pro Suite',
            'exported_at' => $exportTimestamp,
            'tools_count' => count($manifestTools),
            'tools' => $manifestTools,
        ];

        $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $zip->close();

        $downloadFilename = 'whatsapp-tools-export-' . gmdate('Ymd-His') . '.zip';

        return [
            'path' => $tempFile,
            'filename' => $downloadFilename,
            'count' => count($tools),
        ];
    }

    /**
     * Inspect and sanitize an uploaded ZIP package without committing to DB.
     *
     * @param string $uploadedFilePath Path to uploaded ZIP
     * @param int $userId Authenticated user ID to compare existing tool slugs
     * @return array{ok: bool, tools: array<int, array<string, mixed>>, summary: array<string, int>}
     * @throws RuntimeException On any security violation or invalid structure
     */
    public static function inspectToolsZip(string $uploadedFilePath, int $userId): array
    {
        if (!file_exists($uploadedFilePath)) {
            throw new RuntimeException('Uploaded package file could not be found.');
        }

        $fileSize = filesize($uploadedFilePath);
        if ($fileSize === false || $fileSize === 0) {
            throw new RuntimeException('The uploaded file is empty.');
        }
        if ($fileSize > self::MAX_ZIP_SIZE) {
            throw new RuntimeException('Package exceeds the 10MB upload limit.');
        }

        // Verify ZIP Magic Bytes (PK\x03\x04 or PK\x05\x06)
        $fh = fopen($uploadedFilePath, 'rb');
        if (!$fh) {
            throw new RuntimeException('Could not read package file.');
        }
        $magic = fread($fh, 4);
        fclose($fh);
        if ($magic === false || !str_starts_with($magic, "PK\x03\x04") && !str_starts_with($magic, "PK\x05\x06")) {
            throw new RuntimeException('Invalid package file signature. File is not a valid ZIP archive.');
        }

        $zip = new ToolsPackageZipReader($uploadedFilePath);
        $numFiles = $zip->numFiles();
        if ($numFiles > self::MAX_ENTRIES_COUNT) {
            $zip->close();
            throw new RuntimeException('Package contains too many files (max ' . self::MAX_ENTRIES_COUNT . ').');
        }

        $totalUncompressedBytes = 0;
        $discoveredTools = [];

        // Fetch existing tools for this user to detect collisions
        $pdo = Database::pdo();
        $existingStmt = $pdo->prepare('SELECT id, tool_slug, display_name FROM ai_custom_tools WHERE user_id = ?');
        $existingStmt->execute([$userId]);
        $existingRows = $existingStmt->fetchAll(PDO::FETCH_ASSOC);
        $existingMap = [];
        foreach ($existingRows as $er) {
            $existingMap[strtolower((string) $er['tool_slug'])] = $er;
        }

        for ($i = 0; $i < $numFiles; $i++) {
            $stat = $zip->statIndex($i);
            if (!$stat) continue;

            $entryName = $stat['name'];
            $entrySize = (int) $stat['size'];

            // 1. Zip-Slip Guardrails: Strictly reject relative path traversal or drive prefixes
            if (str_contains($entryName, '..') || str_contains($entryName, '\\')) {
                $zip->close();
                throw new RuntimeException('Security alert: Directory traversal attempt detected in package archive.');
            }
            if (str_starts_with($entryName, '/') || preg_match('/^[a-zA-Z]:/', $entryName)) {
                $zip->close();
                throw new RuntimeException('Security alert: Absolute path detected in package archive.');
            }

            // 2. Reject dangerous file extensions
            $lowerName = strtolower($entryName);
            if (preg_match('/\.(php[0-9]?|phtml|phar|exe|sh|bat|cmd|py|pl|cgi|htaccess|svg|html|htm|js)$/i', $lowerName)) {
                $zip->close();
                throw new RuntimeException('Security alert: Disallowed file type (' . htmlspecialchars(basename($entryName)) . ') detected in package.');
            }

            // 3. Zip-Bomb Guardrails: Accumulated size limit
            $totalUncompressedBytes += $entrySize;
            if ($entrySize > self::MAX_SINGLE_ENTRY_SIZE) {
                $zip->close();
                throw new RuntimeException('Security alert: Archive entry exceeds 5MB uncompressed limit.');
            }
            if ($totalUncompressedBytes > self::MAX_UNCOMPRESSED_TOTAL) {
                $zip->close();
                throw new RuntimeException('Security alert: Total uncompressed archive size exceeds 25MB limit.');
            }

            // Only inspect JSON files (manifest.json or files in tools/ or root)
            if (!str_ends_with($lowerName, '.json') || basename($lowerName) === 'manifest.json') {
                continue;
            }

            $content = $zip->getFromIndex($i);
            if ($content === false) continue;

            $decoded = json_decode($content, true);
            if (!is_array($decoded)) continue;

            // Normalize single tool vs array of tools inside one JSON
            $toolsInFile = isset($decoded['tool_slug']) || isset($decoded['slug']) ? [$decoded] : (isset($decoded['tools']) && is_array($decoded['tools']) ? $decoded['tools'] : []);

            foreach ($toolsInFile as $rawTool) {
                if (!is_array($rawTool)) continue;
                $sanitized = self::sanitizeToolPayload($rawTool);
                if ($sanitized !== null) {
                    $slug = $sanitized['tool_slug'];
                    $sanitized['exists'] = isset($existingMap[$slug]);
                    $sanitized['existing_name'] = $existingMap[$slug]['display_name'] ?? null;
                    $sanitized['existing_id'] = $existingMap[$slug]['id'] ?? null;
                    $discoveredTools[$slug] = $sanitized;
                }
            }
        }

        $zip->close();

        if (empty($discoveredTools)) {
            throw new RuntimeException('No valid tool definitions were found in the uploaded package.');
        }

        $toolsList = array_values($discoveredTools);
        $newCount = count(array_filter($toolsList, fn($t) => empty($t['exists'])));
        $updateCount = count($toolsList) - $newCount;

        return [
            'ok' => true,
            'tools' => $toolsList,
            'summary' => [
                'total' => count($toolsList),
                'new' => $newCount,
                'updates' => $updateCount,
            ],
        ];
    }

    /**
     * Commit a sanitized list of tools to the user's catalog.
     *
     * @param int $userId Authenticated user ID
     * @param array<int, array<string, mixed>> $tools List of sanitized tools
     * @param bool $overwriteExisting Whether to overwrite existing tools with matching slug
     * @return array{ok: bool, imported_count: int, new_count: int, updated_count: int}
     */
    public static function commitImportedTools(int $userId, array $tools, bool $overwriteExisting = true): array
    {
        $pdo = Database::pdo();
        $importedCount = 0;
        $newCount = 0;
        $updatedCount = 0;

        $pdo->beginTransaction();
        try {
            $checkStmt = $pdo->prepare('SELECT id FROM ai_custom_tools WHERE user_id = ? AND tool_slug = ? LIMIT 1');
            $insertStmt = $pdo->prepare(
                'INSERT INTO ai_custom_tools 
                 (user_id, tool_slug, display_name, category, description, parameters_schema, execution_config, rbac_roles, is_active)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $updateStmt = $pdo->prepare(
                'UPDATE ai_custom_tools 
                 SET display_name = ?, category = ?, description = ?, parameters_schema = ?, execution_config = ?, rbac_roles = ?, is_active = ?
                 WHERE id = ? AND user_id = ?'
            );

            foreach ($tools as $tool) {
                $sanitized = self::sanitizeToolPayload($tool);
                if ($sanitized === null) continue;

                $slug = $sanitized['tool_slug'];
                $checkStmt->execute([$userId, $slug]);
                $existingRow = $checkStmt->fetch(PDO::FETCH_ASSOC);

                $paramJson = json_encode($sanitized['parameters_schema'], JSON_UNESCAPED_SLASHES);
                $execJson = json_encode($sanitized['execution_config'], JSON_UNESCAPED_SLASHES);
                $rolesJson = json_encode($sanitized['rbac_roles']);
                $isActive = (int) ($sanitized['is_active'] ?? 1);

                if ($existingRow) {
                    if (!$overwriteExisting) {
                        continue;
                    }
                    $updateStmt->execute([
                        $sanitized['display_name'],
                        $sanitized['category'],
                        $sanitized['description'],
                        $paramJson,
                        $execJson,
                        $rolesJson,
                        $isActive,
                        $existingRow['id'],
                        $userId
                    ]);
                    $updatedCount++;
                    $importedCount++;
                } else {
                    $insertStmt->execute([
                        $userId,
                        $slug,
                        $sanitized['display_name'],
                        $sanitized['category'],
                        $sanitized['description'],
                        $paramJson,
                        $execJson,
                        $rolesJson,
                        $isActive
                    ]);
                    $newCount++;
                    $importedCount++;
                }
            }

            $pdo->commit();

            return [
                'ok' => true,
                'imported_count' => $importedCount,
                'new_count' => $newCount,
                'updated_count' => $updatedCount,
            ];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw new RuntimeException('Failed to save imported tools: ' . $e->getMessage());
        }
    }

    /**
     * Sanitize and validate an individual tool payload.
     *
     * @param array<string, mixed> $raw
     * @return array<string, mixed>|null Sanitized tool or null if invalid
     */
    public static function sanitizeToolPayload(array $raw): ?array
    {
        $rawSlug = (string) ($raw['tool_slug'] ?? $raw['slug'] ?? '');
        $slug = preg_replace('/[^a-z0-9_]/', '_', strtolower(trim($rawSlug)));
        $slug = substr($slug, 0, 64);
        if ($slug === '') {
            return null;
        }

        $rawName = (string) ($raw['display_name'] ?? $raw['title'] ?? $raw['name'] ?? ucwords(str_replace('_', ' ', $slug)));
        $displayName = trim(strip_tags($rawName));
        $displayName = substr($displayName, 0, 100);
        if ($displayName === '') {
            $displayName = ucwords(str_replace('_', ' ', $slug));
        }

        $category = (string) ($raw['category'] ?? 'rest_api');
        if (!in_array($category, self::ALLOWED_CATEGORIES, true)) {
            $category = 'rest_api';
        }

        $description = trim(strip_tags((string) ($raw['description'] ?? '')));
        $description = substr($description, 0, 1000);

        // Parameters schema
        $schema = $raw['parameters_schema'] ?? [];
        if (!is_array($schema)) {
            $schema = [];
        }
        $properties = $schema['properties'] ?? [];
        if (!is_array($properties) && !is_object($properties)) {
            $properties = [];
        }
        $sanitizedProperties = [];
        foreach ((array) $properties as $pName => $pDef) {
            $cleanPName = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $pName);
            if ($cleanPName === '') continue;
            $pType = strtoupper((string) ($pDef['type'] ?? 'STRING'));
            if (!in_array($pType, ['STRING', 'NUMBER', 'INTEGER', 'BOOLEAN', 'OBJECT', 'ARRAY'], true)) {
                $pType = 'STRING';
            }
            $sanitizedProperties[$cleanPName] = [
                'type' => $pType,
                'description' => trim(strip_tags((string) ($pDef['description'] ?? ''))),
            ];
        }

        $required = (array) ($schema['required'] ?? []);
        $sanitizedRequired = array_values(array_intersect($required, array_keys($sanitizedProperties)));

        $parametersSchema = [
            'type' => 'OBJECT',
            'properties' => (object) $sanitizedProperties,
            'required' => $sanitizedRequired,
        ];

        // Execution config & SSRF Guardrails
        $exec = is_array($raw['execution_config'] ?? null) ? $raw['execution_config'] : [];
        $executionConfig = [];

        if ($category === 'rest_api') {
            $url = trim((string) ($exec['url'] ?? ''));
            // Require http or https, block non-web protocols
            if (!preg_match('#^https?://#i', $url)) {
                $url = 'https://api.example.com/' . $slug;
            }
            $method = strtoupper(trim((string) ($exec['method'] ?? 'GET')));
            if (!in_array($method, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], true)) {
                $method = 'GET';
            }
            $timeoutMs = max(1000, min(30000, (int) ($exec['timeout_ms'] ?? 5000)));

            $headers = is_array($exec['headers'] ?? null) ? $exec['headers'] : ['Accept' => 'application/json'];
            // Sanitize headers
            $sanitizedHeaders = [];
            foreach ($headers as $hKey => $hVal) {
                $cleanHKey = trim(strip_tags((string) $hKey));
                $cleanHVal = trim(strip_tags((string) $hVal));
                if ($cleanHKey !== '') {
                    $sanitizedHeaders[$cleanHKey] = $cleanHVal;
                }
            }

            $executionConfig = [
                'url' => $url,
                'method' => $method,
                'headers' => $sanitizedHeaders ?: ['Accept' => 'application/json'],
                'timeout_ms' => $timeoutMs,
                'response_path' => trim(strip_tags((string) ($exec['response_path'] ?? ''))),
            ];
            if (!empty($exec['body_template'])) {
                $executionConfig['body_template'] = $exec['body_template'];
            }
        } elseif ($category === 'shared_env_api') {
            $baseUrl = trim((string) ($exec['base_url'] ?? ''));
            if (!preg_match('#^https?://#i', $baseUrl)) {
                $baseUrl = 'https://api.example.com';
            }
            $executionConfig = [
                'active_env' => 'production',
                'base_url' => $baseUrl,
                'bearer_token' => trim((string) ($exec['bearer_token'] ?? '')),
                'timeout_ms' => max(1000, min(30000, (int) ($exec['timeout_ms'] ?? 8000))),
            ];
        } elseif ($category === 'webhook') {
            $targetUrl = trim((string) ($exec['target_url'] ?? ''));
            if (!preg_match('#^https?://#i', $targetUrl)) {
                $targetUrl = 'https://webhook.site/placeholder';
            }
            $executionConfig = [
                'target_url' => $targetUrl,
                'timeout_ms' => max(1000, min(30000, (int) ($exec['timeout_ms'] ?? 5000))),
            ];
        } else {
            $executionConfig = [
                'handler' => preg_replace('/[^a-z0-9_]/', '_', strtolower(trim((string) ($exec['handler'] ?? $slug)))),
            ];
        }

        // RBAC roles
        $roles = (array) ($raw['rbac_roles'] ?? ['guest', 'customer', 'admin']);
        $sanitizedRoles = array_values(array_intersect($roles, self::ALLOWED_ROLES));
        if (empty($sanitizedRoles)) {
            $sanitizedRoles = ['guest', 'customer', 'admin'];
        }

        return [
            'tool_slug' => $slug,
            'display_name' => $displayName,
            'category' => $category,
            'description' => $description,
            'parameters_schema' => $parametersSchema,
            'execution_config' => $executionConfig,
            'rbac_roles' => $sanitizedRoles,
            'is_active' => !empty($raw['is_active']) ? 1 : 0,
        ];
    }
}


/**
 * Robust ZIP Writer supporting native ZipArchive with pure-PHP fallback
 * for zero-dependency environments (e.g. Render, slim containers, minimal PHP).
 */
class ToolsPackageZipWriter
{
    private ?ZipArchive $native = null;
    private string $filePath;
    private array $entries = [];

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
        if (class_exists('ZipArchive')) {
            $this->native = new ZipArchive();
            $res = $this->native->open($filePath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
            if ($res !== true) {
                $this->native = null;
            }
        }
    }

    public function addFromString(string $name, string $content): void
    {
        if ($this->native !== null) {
            $this->native->addFromString($name, $content);
        } else {
            $this->entries[$name] = $content;
        }
    }

    public function close(): void
    {
        if ($this->native !== null) {
            $this->native->close();
            $this->native = null;
            return;
        }

        $data = '';
        $centralDir = '';
        $offset = 0;
        $count = count($this->entries);

        $time = time();
        $dosTime = ((date('H', $time) << 11) | (date('i', $time) << 5) | (date('s', $time) >> 1));
        $dosDate = (((date('Y', $time) - 1980) << 9) | (date('m', $time) << 5) | date('d', $time));

        foreach ($this->entries as $name => $content) {
            $uncompLen = strlen($content);
            $crc = crc32($content);
            $compressed = function_exists('gzdeflate') ? gzdeflate($content) : false;
            if ($compressed !== false && strlen($compressed) < $uncompLen) {
                $method = 8;
                $compLen = strlen($compressed);
                $body = $compressed;
            } else {
                $method = 0;
                $compLen = $uncompLen;
                $body = $content;
            }

            $nameLen = strlen($name);

            $localHeader = pack(
                'VvvvvvVVVvv',
                0x04034b50, 20, 0, $method, $dosTime, $dosDate, $crc, $compLen, $uncompLen, $nameLen, 0
            ) . $name;

            $data .= $localHeader . $body;

            $centralDir .= pack(
                'VvvvvvvVVVvvvvvVV',
                0x02014b50, 20, 20, 0, $method, $dosTime, $dosDate, $crc, $compLen, $uncompLen, $nameLen, 0, 0, 0, 0, 32, $offset
            ) . $name;

            $offset = strlen($data);
        }

        $eocd = pack(
            'VvvvvVVv',
            0x06054b50, 0, 0, $count, $count, strlen($centralDir), $offset, 0
        );

        file_put_contents($this->filePath, $data . $centralDir . $eocd);
    }
}

/**
 * Robust ZIP Reader supporting native ZipArchive with pure-PHP fallback
 * for zero-dependency environments (e.g. Render, slim containers, minimal PHP).
 */
class ToolsPackageZipReader
{
    private ?ZipArchive $native = null;
    private array $entries = [];

    public function __construct(string $filePath)
    {
        if (class_exists('ZipArchive')) {
            $this->native = new ZipArchive();
            $res = $this->native->open($filePath, ZipArchive::RDONLY);
            if ($res === true) {
                return;
            }
            $this->native = null;
        }

        $this->parsePure($filePath);
    }

    public function numFiles(): int
    {
        if ($this->native !== null) {
            return $this->native->numFiles;
        }
        return count($this->entries);
    }

    public function statIndex(int $index): ?array
    {
        if ($this->native !== null) {
            $s = $this->native->statIndex($index);
            return $s !== false ? $s : null;
        }
        if (!isset($this->entries[$index])) return null;
        return [
            'name' => $this->entries[$index]['name'],
            'size' => $this->entries[$index]['size'],
        ];
    }

    public function getFromIndex(int $index): false|string
    {
        if ($this->native !== null) {
            return $this->native->getFromIndex($index);
        }
        if (!isset($this->entries[$index])) return false;
        return $this->entries[$index]['content'];
    }

    public function close(): void
    {
        if ($this->native !== null) {
            $this->native->close();
            $this->native = null;
        }
    }

    private function parsePure(string $filePath): void
    {
        $raw = @file_get_contents($filePath);
        if ($raw === false || strlen($raw) < 22) {
            throw new RuntimeException('Could not read package file archive.');
        }

        $len = strlen($raw);
        $eocdPos = strrpos($raw, "PK\x05\x06");
        if ($eocdPos === false) {
            throw new RuntimeException('Invalid package file signature: End of Central Directory record not found.');
        }

        $eocd = unpack('vdisk/vcdDisk/vcdEntriesDisk/vtotalEntries/VcdSize/VcdOffset/vcommentLen', substr($raw, $eocdPos + 4, 18));
        if (!$eocd) {
            throw new RuntimeException('Corrupt ZIP archive header.');
        }

        $cdOffset = $eocd['cdOffset'];
        $cdCount = $eocd['totalEntries'];

        for ($i = 0; $i < $cdCount; $i++) {
            if ($cdOffset + 46 > $len) break;
            if (substr($raw, $cdOffset, 4) !== "PK\x01\x02") break;

            $cd = unpack(
                'vmade/vextract/vflags/vmethod/vmodtime/vmoddate/Vcrc/VcompSize/VuncompSize/vnameLen/vextraLen/vcommLen/vdisk/vintAttr/VextAttr/VlocalOffset',
                substr($raw, $cdOffset + 4, 42)
            );
            if (!$cd) break;

            $name = substr($raw, $cdOffset + 46, $cd['nameLen']);
            $localOffset = $cd['localOffset'];

            if ($localOffset + 30 <= $len && substr($raw, $localOffset, 4) === "PK\x03\x04") {
                $loc = unpack('vextract/vflags/vmethod/vmodtime/vmoddate/Vcrc/VcompSize/VuncompSize/vnameLen/vextraLen', substr($raw, $localOffset + 4, 26));
                $dataStart = $localOffset + 30 + $loc['nameLen'] + $loc['extraLen'];
                $compressedData = substr($raw, $dataStart, $cd['compSize']);

                $content = null;
                if ($cd['method'] === 0) {
                    $content = $compressedData;
                } elseif ($cd['method'] === 8) {
                    $content = function_exists('gzinflate') ? @gzinflate($compressedData) : false;
                }

                if ($content !== false && $content !== null) {
                    $this->entries[] = [
                        'name' => $name,
                        'size' => $cd['uncompSize'],
                        'content' => $content
                    ];
                }
            }

            $cdOffset += 46 + $cd['nameLen'] + $cd['extraLen'] + $cd['commLen'];
        }
    }
}
