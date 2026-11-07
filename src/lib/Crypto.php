<?php

declare(strict_types=1);

/**
 * Enterprise At-Rest Encryption Helper (AES-256-GCM)
 *
 * Encrypts and decrypts sensitive tenant credentials (AI API keys, SMTP passwords).
 * Employs authenticated encryption with associated data (AEAD) via AES-256-GCM.
 * Compatible with Node.js crypto module.
 */

final class Crypto
{
    private const CIPHER = 'aes-256-gcm';
    private const PREFIX = 'enc:v1:';
    private const IV_LEN = 12; // 96 bits for GCM
    private const TAG_LEN = 16; // 128-bit authentication tag

    /**
     * Resolves the 32-byte binary key derived from APP_KEY or system secrets.
     */
    private static function getKey(): string
    {
        $raw = getenv('APP_KEY')
            ?: getenv('SYSTEM_API_SECRET')
            ?: getenv('WORKER_TOKEN')
            ?: 'default-whats-gateway-secure-key-32b';

        return hash('sha256', (string) $raw, true);
    }

    /**
     * Encrypts plaintext using AES-256-GCM with a fresh random IV.
     * Returns: enc:v1:<base64(iv . tag . ciphertext)>
     */
    public static function encrypt(string $plaintext): string
    {
        if ($plaintext === '') {
            return '';
        }

        $key = self::getKey();
        $iv = random_bytes(self::IV_LEN);
        $tag = '';

        $ciphertext = openssl_encrypt(
            $plaintext,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            self::TAG_LEN
        );

        if ($ciphertext === false) {
            throw new RuntimeException('Failed to encrypt credential with AES-256-GCM.');
        }

        return self::PREFIX . base64_encode($iv . $tag . $ciphertext);
    }

    /**
     * Decrypts AES-256-GCM ciphertext.
     * Gracefully returns unencrypted input if not prefixed with enc:v1: for backward compatibility.
     */
    public static function decrypt(string $data): string
    {
        if ($data === '' || !str_starts_with($data, self::PREFIX)) {
            return $data; // Legacy unencrypted plaintext fallback
        }

        $raw = substr($data, strlen(self::PREFIX));
        $combined = base64_decode($raw, true);
        if ($combined === false || strlen($combined) < (self::IV_LEN + self::TAG_LEN)) {
            return $data;
        }

        $iv = substr($combined, 0, self::IV_LEN);
        $tag = substr($combined, self::IV_LEN, self::TAG_LEN);
        $ciphertext = substr($combined, self::IV_LEN + self::TAG_LEN);

        $key = self::getKey();
        $decrypted = openssl_decrypt(
            $ciphertext,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        return ($decrypted !== false) ? $decrypted : $data;
    }
}

/**
 * Functional helpers for direct access.
 */
function encrypt_credential(string $plaintext): string
{
    return Crypto::encrypt($plaintext);
}

function decrypt_credential(string $data): string
{
    return Crypto::decrypt($data);
}
