<?php

/**
 * WhatsApp API Hub — Standalone Image & Message Sender
 * 
 * Upload an image from your computer and send it to any WhatsApp number
 * using your live API key.
 *
 * Can be run via browser: http://localhost/wa/examples/php/send_message.php
 * or hosted on any PHP server.
 */

declare(strict_types=1);

// ==============================================================================
// DEFAULT CONFIGURATION
// ==============================================================================
$defaultApiKey = 'wa_live_YOUR_ACTUAL_API_KEY_HERE';
$defaultApiUrl = 'https://whatsapp-api-xepo.onrender.com/api/send';

$responseResult = null;
$error = null;

// ==============================================================================
// FORM SUBMISSION HANDLER
// ==============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $apiKey = trim((string) ($_POST['api_key'] ?? $defaultApiKey));
    $apiUrl = trim((string) ($_POST['api_url'] ?? $defaultApiUrl));
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $caption = trim((string) ($_POST['caption'] ?? ''));
    $humanize = !empty($_POST['humanize']);

    // Sanitize phone number (remove spaces, dashes, plus sign)
    $cleanPhone = preg_replace('/\D/', '', $phone) ?? '';

    if ($cleanPhone === '') {
        $error = 'Please enter a valid recipient phone number with country code.';
    } elseif ($apiKey === '') {
        $error = 'API Key is required.';
    } elseif (empty($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Please select an image file to upload.';
    } else {
        $tmpFile = $_FILES['image']['tmp_name'];
        $fileName = basename((string) $_FILES['image']['name']);
        $fileMime = mime_content_type($tmpFile) ?: $_FILES['image']['type'] ?: 'image/jpeg';

        // Prepare multipart cURL payload
        $cFile = new CURLFile($tmpFile, $fileMime, $fileName);

        $postData = [
            'to' => $cleanPhone,
            'caption' => $caption,
            'file' => $cFile,
            'type' => 'image',
            'humanize' => $humanize ? '1' : '0',
        ];

        // Execute cURL request to WhatsApp API Hub
        $ch = curl_init($apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => [
                'X-API-Key: ' . $apiKey,
                'User-Agent: WhatsApp-API-Hub-Client/1.0',
            ],
        ]);

        $rawBody = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            $error = 'Connection Error: ' . $curlError;
        } else {
            $responseResult = [
                'status_code' => $httpCode,
                'data' => json_decode((string) $rawBody, true) ?: $rawBody,
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send WhatsApp Image — Developer Tool</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    colors: {
                        brand: { wa_dark: '#075E54', wa_med: '#128C7E', wa_light: '#25D366' }
                    }
                }
            }
        };
    </script>
</head>
<body class="min-h-full flex flex-col items-center justify-center p-4 sm:p-6 lg:p-8 font-sans text-slate-800 bg-slate-100/60">

    <div class="w-full max-w-xl bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
        
        <!-- Header -->
        <div class="bg-gradient-to-r from-brand-wa_dark to-brand-wa_med p-6 text-white">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center border border-white/20">
                    <svg class="w-6 h-6 text-brand-wa_light" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <div>
                    <h1 class="text-lg sm:text-xl font-bold tracking-tight">Send WhatsApp Image</h1>
                    <p class="text-xs text-white/80">Direct multipart image upload via WhatsApp API Hub</p>
                </div>
            </div>
        </div>

        <!-- Form Container -->
        <form method="POST" enctype="multipart/form-data" class="p-6 space-y-5">

            <!-- Alerts -->
            <?php if ($error): ?>
                <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm flex items-start gap-2.5">
                    <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <?php if ($responseResult): ?>
                <?php $isOk = ($responseResult['status_code'] >= 200 && $responseResult['status_code'] < 300); ?>
                <div class="p-4 rounded-xl <?= $isOk ? 'bg-emerald-50 border border-emerald-200 text-emerald-900' : 'bg-rose-50 border border-rose-200 text-rose-900' ?> text-sm space-y-2">
                    <div class="flex items-center justify-between font-bold">
                        <span class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full <?= $isOk ? 'bg-emerald-500' : 'bg-rose-500' ?>"></span>
                            <?= $isOk ? 'Image Dispatched to Queue Successfully!' : 'Delivery Failed' ?>
                        </span>
                        <span class="font-mono text-xs px-2 py-0.5 rounded bg-white/60 border border-current">HTTP <?= $responseResult['status_code'] ?></span>
                    </div>
                    <pre class="bg-slate-900 text-slate-100 p-3 rounded-lg font-mono text-xs overflow-x-auto mt-2 leading-relaxed"><?= htmlspecialchars(json_encode($responseResult['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) ?></pre>
                </div>
            <?php endif; ?>

            <!-- API Configuration -->
            <div class="grid grid-cols-1 gap-4 pt-1">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">API Endpoint</label>
                    <input type="url" name="api_url" value="<?= htmlspecialchars($_POST['api_url'] ?? $defaultApiUrl) ?>" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:border-brand-wa_med focus:ring-2 focus:ring-brand-wa_light/20 outline-none transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">API Key</label>
                    <input type="text" name="api_key" value="<?= htmlspecialchars($_POST['api_key'] ?? $defaultApiKey) ?>" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:border-brand-wa_med focus:ring-2 focus:ring-brand-wa_light/20 outline-none transition-all">
                </div>
            </div>

            <!-- Recipient Phone Number -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Recipient Phone Number (with Country Code)</label>
                <div class="relative">
                    <input type="tel" name="phone" placeholder="919876543210 or 15551234567" required
                           value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                           class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:border-brand-wa_med focus:ring-2 focus:ring-brand-wa_light/20 outline-none transition-all font-medium">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                </div>
                <p class="text-[11px] text-slate-500 mt-1">Include country code without '+' or spaces (e.g. 91 for India, 1 for USA).</p>
            </div>

            <!-- File Upload & Preview -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Select Image to Upload</label>
                <div class="border-2 border-dashed border-slate-300 hover:border-brand-wa_med rounded-xl p-4 text-center cursor-pointer transition-colors bg-slate-50/50 hover:bg-white" onclick="document.getElementById('imageInput').click()">
                    <input type="file" id="imageInput" name="image" accept="image/jpeg,image/png,image/webp,image/gif" class="hidden" onchange="previewImage(this)" required>
                    <div id="uploadPrompt" class="space-y-1">
                        <svg class="w-8 h-8 mx-auto text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                        <p class="text-xs font-semibold text-slate-700">Click to browse or drag & drop</p>
                        <p class="text-[11px] text-slate-400">JPG, PNG, WEBP, GIF up to 20MB</p>
                    </div>
                    <div id="imagePreviewContainer" class="hidden">
                        <img id="imagePreview" src="" alt="Preview" class="max-h-48 mx-auto rounded-lg shadow-sm border border-slate-200 object-contain">
                        <p id="fileName" class="text-xs font-semibold text-slate-600 mt-2 truncate"></p>
                    </div>
                </div>
            </div>

            <!-- Caption / Message -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Image Caption / Message (Optional)</label>
                <textarea name="caption" rows="3" placeholder="Add a caption to accompany the image... (supports *bold*, _italic_)"
                          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:border-brand-wa_med focus:ring-2 focus:ring-brand-wa_light/20 outline-none transition-all"><?= htmlspecialchars($_POST['caption'] ?? '') ?></textarea>
            </div>

            <!-- Humanized typing delay -->
            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" id="humanize" name="humanize" value="1" <?= (!isset($_POST['phone']) || !empty($_POST['humanize'])) ? 'checked' : '' ?>
                       class="w-4 h-4 text-brand-wa_med accent-brand-wa_med rounded border-slate-300 cursor-pointer">
                <label for="humanize" class="text-xs font-medium text-slate-700 cursor-pointer">
                    Simulate human typing delay (Anti-Ban Protection)
                </label>
            </div>

            <!-- Submit Button -->
            <button type="submit" id="submitBtn"
                    class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-brand-wa_med to-brand-wa_light hover:opacity-95 text-white font-bold text-sm shadow-md shadow-brand-wa_light/20 transition-all flex items-center justify-center gap-2 active:scale-[0.99]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                <span>Upload & Send WhatsApp Image</span>
            </button>
        </form>

        <div class="p-4 bg-slate-50 border-t border-slate-200 text-center text-xs text-slate-400">
            Powered by <a href="https://whatsapp-api-xepo.onrender.com" target="_blank" class="font-semibold text-slate-600 hover:underline">WhatsApp API Hub</a> &bull; Baileys Engine
        </div>

    </div>

    <script>
        function previewImage(input) {
            const prompt = document.getElementById('uploadPrompt');
            const container = document.getElementById('imagePreviewContainer');
            const preview = document.getElementById('imagePreview');
            const fileName = document.getElementById('fileName');

            if (input.files && input.files[0]) {
                const file = input.files[0];
                const reader = new FileReader();

                reader.onload = function(e) {
                    preview.src = e.target.result;
                    fileName.textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
                    prompt.classList.add('hidden');
                    container.classList.remove('hidden');
                };

                reader.readAsDataURL(file);
            }
        }
    </script>

</body>
</html>
