/**
 * WhatsApp Presence Simulation Engine
 * ------------------------------------
 * Simulates human-like "composing" (typing...) presence updates on WhatsApp
 * while the AI agent reasons, retrieves knowledge, or calls tools.
 * Safely guards against socket disconnects and stale presence states.
 */

export class ChatPresenceManager {
  /**
   * Start typing presence for a remote JID
   * @param {Object} socket - Active Baileys socket
   * @param {string} remoteJid - WhatsApp recipient JID
   * @returns {Promise<() => Promise<void>>} Stop typing function
   */
  static async startTyping(socket, remoteJid) {
    if (!socket || typeof socket.sendPresenceUpdate !== 'function') {
      return async () => {};
    }

    try {
      await socket.sendPresenceUpdate('available').catch(() => {});
      await socket.sendPresenceUpdate('composing', remoteJid).catch(() => {});
    } catch (_) {}

    let stopped = false;
    const stopFn = async () => {
      if (stopped) return;
      stopped = true;
      try {
        if (socket && typeof socket.sendPresenceUpdate === 'function') {
          await socket.sendPresenceUpdate('paused', remoteJid).catch(() => {});
        }
      } catch (_) {}
    };

    // Auto-stop safety timer after 15 seconds so typing never stays stuck forever
    setTimeout(stopFn, 15000);

    return stopFn;
  }
}
