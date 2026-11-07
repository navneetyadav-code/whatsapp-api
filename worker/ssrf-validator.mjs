import dns from 'node:dns/promises';
import net from 'node:net';

/**
 * Checks whether an IP address is in a private, loopback, or reserved range.
 * Covers IPv4 (RFC 1918, RFC 3927, RFC 6598, loopback 127/8) and IPv6 (RFC 4193, RFC 4291).
 */
export function isPrivateIp(ip) {
  let cleanIp = String(ip || '').trim().toLowerCase();
  if (cleanIp.startsWith('[') && cleanIp.endsWith(']')) {
    cleanIp = cleanIp.slice(1, -1);
  }

  if (!net.isIP(cleanIp)) {
    return true;
  }

  // Handle IPv4-mapped IPv6 addresses (e.g. ::ffff:127.0.0.1)
  if (cleanIp.startsWith('::ffff:')) {
    const v4 = cleanIp.slice(7);
    if (net.isIPv4(v4)) {
      return isPrivateIp(v4);
    }
  }

  if (cleanIp === '::1' || cleanIp === '::' || cleanIp === '0.0.0.0') {
    return true;
  }

  if (net.isIPv4(cleanIp)) {
    const parts = cleanIp.split('.').map(Number);
    if (parts.length !== 4 || parts.some((n) => isNaN(n) || n < 0 || n > 255)) {
      return true;
    }
    // 0.0.0.0/8 (Broadcast/source)
    if (parts[0] === 0) return true;
    // 10.0.0.0/8 (Private)
    if (parts[0] === 10) return true;
    // 100.64.0.0/10 (Carrier-grade NAT / Shared)
    if (parts[0] === 100 && parts[1] >= 64 && parts[1] <= 127) return true;
    // 127.0.0.0/8 (Loopback)
    if (parts[0] === 127) return true;
    // 169.254.0.0/16 (Link-local / Cloud metadata: 169.254.169.254)
    if (parts[0] === 169 && parts[1] === 254) return true;
    // 172.16.0.0/12 (Private)
    if (parts[0] === 172 && parts[1] >= 16 && parts[1] <= 31) return true;
    // 192.168.0.0/16 (Private)
    if (parts[0] === 192 && parts[1] === 168) return true;
    // 192.0.0.0/24 (IETF Protocol Assignments)
    if (parts[0] === 192 && parts[1] === 0 && parts[2] === 0) return true;
    // 198.18.0.0/15 (Benchmarking)
    if (parts[0] === 198 && (parts[1] === 18 || parts[1] === 19)) return true;
    // 224.0.0.0/4 (Multicast) & 240.0.0.0/4 (Reserved)
    if (parts[0] >= 224) return true;

    return false;
  }

  // IPv6 unique local (fc00::/7)
  if (cleanIp.startsWith('fc') || cleanIp.startsWith('fd')) return true;
  // IPv6 link-local (fe80::/10)
  if (cleanIp.startsWith('fe8') || cleanIp.startsWith('fe9') || cleanIp.startsWith('fea') || cleanIp.startsWith('feb')) return true;

  return false;
}

/**
 * Validates that target URL is a safe public endpoint:
 * - Requires HTTP/HTTPS scheme
 * - Rejects localhost, loopback, private VPC addresses, and cloud metadata (169.254.169.254)
 */
export async function assertSafeWebhookUrl(targetUrl, { allowHttp = false } = {}) {
  let parsed;
  try {
    parsed = new URL(targetUrl);
  } catch {
    throw new Error('Target URL is invalid.');
  }

  const host = parsed.hostname.toLowerCase();
  if (parsed.protocol !== 'https:' && (!allowHttp || parsed.protocol !== 'http:')) {
    throw new Error('Target URL must use HTTPS.');
  }

  if (!host || ['localhost', '127.0.0.1', '::1'].includes(host) || host.endsWith('.local')) {
    throw new Error('Target URL cannot point to localhost or private hosts.');
  }

  const addresses = net.isIP(host)
    ? [{ address: host }]
    : await dns.lookup(host, { all: true, verbatim: true });

  if (addresses.some((entry) => isPrivateIp(entry.address))) {
    throw new Error('Target URL cannot resolve to a private or reserved IP address.');
  }
}
