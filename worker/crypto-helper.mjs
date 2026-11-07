import crypto from 'node:crypto';

const CIPHER = 'aes-256-gcm';
const PREFIX = 'enc:v1:';
const IV_LEN = 12; // 96 bits for GCM
const TAG_LEN = 16; // 128-bit authentication tag

function getKey() {
  const raw = process.env.APP_KEY
    || process.env.SYSTEM_API_SECRET
    || process.env.WORKER_TOKEN
    || 'default-whats-gateway-secure-key-32b';
  return crypto.createHash('sha256').update(String(raw)).digest();
}

/**
 * Encrypts a string using AES-256-GCM.
 * Output: enc:v1:<base64(iv . tag . ciphertext)>
 */
export function encryptCredential(plaintext) {
  if (!plaintext || typeof plaintext !== 'string') {
    return '';
  }

  const key = getKey();
  const iv = crypto.randomBytes(IV_LEN);
  const cipher = crypto.createCipheriv(CIPHER, key, iv);

  const ciphertext = Buffer.concat([cipher.update(plaintext, 'utf8'), cipher.final()]);
  const tag = cipher.getAuthTag();

  const combined = Buffer.concat([iv, tag, ciphertext]);
  return PREFIX + combined.toString('base64');
}

/**
 * Decrypts an AES-256-GCM encrypted string.
 * Gracefully returns original plaintext if not prefixed with enc:v1: (backward compatibility).
 */
export function decryptCredential(data) {
  if (!data || typeof data !== 'string' || !data.startsWith(PREFIX)) {
    return data || '';
  }

  try {
    const raw = data.slice(PREFIX.length);
    const combined = Buffer.from(raw, 'base64');
    if (combined.length < (IV_LEN + TAG_LEN)) {
      return data;
    }

    const iv = combined.subarray(0, IV_LEN);
    const tag = combined.subarray(IV_LEN, IV_LEN + TAG_LEN);
    const ciphertext = combined.subarray(IV_LEN + TAG_LEN);

    const key = getKey();
    const decipher = crypto.createDecipheriv(CIPHER, key, iv);
    decipher.setAuthTag(tag);

    const decrypted = Buffer.concat([decipher.update(ciphertext), decipher.final()]);
    return decrypted.toString('utf8');
  } catch {
    return data; // Return original on error (prevents crashing if key mismatch)
  }
}
