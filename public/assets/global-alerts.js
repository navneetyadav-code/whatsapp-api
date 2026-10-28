/**
 * WhatsGateway — Universal Global Alerts & Buzzer System
 *
 * Runs across ALL dashboard pages. Provides hardware-accelerated WebAudio buzzer
 * alarms, gentle incoming message chimes, persistent desktop push notifications,
 * tab title emergency blinking, and global mute controls.
 */
(function (window, document) {
    'use strict';

    // 1. Configuration & URLs
    const config = window.AppConfig || {};
    const baseUrl = config.baseUrl || '/whatsapp-api/public';
    const apiBase = config.apiBase || `${baseUrl}/api/chat`;

    // 2. Audio & Alert State
    let audioCtx = null;
    let buzzerInterval = null;
    let isBuzzerMuted = localStorage.getItem('whats_buzzer_muted') === '1';
    let isBuzzerRunning = false;
    let activeEscalations = [];
    let notifiedEscalationPhones = new Set();
    let lastKnownUnreadTotal = -1;
    let knownMessageTimestamps = new Map();
    let pollTimer = null;

    // Document title emergency blinking
    let originalTitle = document.title;
    let titleBlinkTimer = null;
    let titleBlinkState = false;
    let currentBlinkText = '';

    // Check if current page is live-chat (has active live-chat.js)
    function isLiveChatPage() {
        return window.location.search.includes('page=live-chat') ||
               window.location.pathname.endsWith('/live-chat') ||
               document.getElementById('messagesContainer') !== null;
    }

    // =========================================================================
    // WEBAUDIO SYNTHESIZER ENGINE (Hardware Buzzer & Message Chime)
    // =========================================================================

    function initAudioContext() {
        if (!audioCtx) {
            const AudioContextClass = window.AudioContext || window.webkitAudioContext;
            if (AudioContextClass) {
                audioCtx = new AudioContextClass();
            }
        }
        if (audioCtx && audioCtx.state === 'suspended') {
            audioCtx.resume().catch(() => {});
        }
        return audioCtx;
    }

    // Unlock audio context on ANY user gesture across the entire dashboard
    function unlockAudioOnGesture() {
        initAudioContext();
        if (audioCtx && audioCtx.state === 'suspended') {
            audioCtx.resume().catch(() => {});
        }
    }
    ['click', 'keydown', 'touchstart'].forEach(evt => {
        document.addEventListener(evt, unlockAudioOnGesture, { passive: true });
    });

    /**
     * Synthesizes hardware alert buzzer: 880Hz (A5) -> 1320Hz (E6) oscillating tone
     */
    function playBuzzerTone() {
        if (isBuzzerMuted) return;
        try {
            initAudioContext();
            if (!audioCtx || audioCtx.state !== 'running') return;

            const now = audioCtx.currentTime;
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();

            osc.type = 'square';
            // Dual-tone frequency jump: 880Hz -> 1320Hz
            osc.frequency.setValueAtTime(880, now);
            osc.frequency.setValueAtTime(1320, now + 0.16);

            // Envelope: ramp up and smooth decay
            gain.gain.setValueAtTime(0.001, now);
            gain.gain.exponentialRampToValueAtTime(0.35, now + 0.05);
            gain.gain.exponentialRampToValueAtTime(0.001, now + 0.38);

            osc.connect(gain);
            gain.connect(audioCtx.destination);

            osc.start(now);
            osc.stop(now + 0.40);
        } catch (e) {
            console.warn('[WhatsAlerts Audio Error]:', e);
        }
    }

    /**
     * Synthesizes gentle two-tone message chime (523Hz -> 784Hz) for new messages
     */
    function playMessageChime() {
        if (isBuzzerMuted) return;
        try {
            initAudioContext();
            if (!audioCtx || audioCtx.state !== 'running') return;

            const now = audioCtx.currentTime;
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();

            osc.type = 'sine';
            osc.frequency.setValueAtTime(523.25, now); // C5
            osc.frequency.setValueAtTime(783.99, now + 0.10); // G5

            gain.gain.setValueAtTime(0.001, now);
            gain.gain.exponentialRampToValueAtTime(0.20, now + 0.04);
            gain.gain.exponentialRampToValueAtTime(0.001, now + 0.45);

            osc.connect(gain);
            gain.connect(audioCtx.destination);

            osc.start(now);
            osc.stop(now + 0.48);
        } catch (e) {
            console.warn('[WhatsAlerts Chime Error]:', e);
        }
    }

    // =========================================================================
    // NATIVE DESKTOP NOTIFICATIONS (Windows/Mac Action Center)
    // =========================================================================

    function requestDesktopNotifications() {
        if (!('Notification' in window)) return Promise.resolve('unsupported');
        return Notification.requestPermission().then(permission => {
            updateNotificationPermissionUi();
            return permission;
        });
    }

    function fireDesktopNotification(title, body, tag, targetUrl = null) {
        if (!('Notification' in window) || Notification.permission !== 'granted') return;
        try {
            const notif = new Notification(title, {
                body: body,
                tag: tag || 'whatsgateway-alert',
                requireInteraction: true, // Keep visible on screen until user interacts!
                icon: `${baseUrl}/assets/logo.jpg`,
                badge: `${baseUrl}/assets/logo.jpg`
            });

            notif.onclick = function () {
                window.focus();
                if (targetUrl) {
                    window.location.href = targetUrl;
                } else {
                    window.location.href = `${baseUrl}/index.php?page=live-chat`;
                }
                notif.close();
            };
        } catch (err) {
            console.warn('[WhatsAlerts Notification Error]:', err);
        }
    }

    // =========================================================================
    // TAB TITLE BLINKER (Visible even when browser is minimized or tab inactive)
    // =========================================================================

    function startTitleBlink(text) {
        currentBlinkText = text;
        if (titleBlinkTimer) return;
        if (!originalTitle || originalTitle.includes('🚨') || originalTitle.includes('🔔')) {
            originalTitle = 'WhatsGateway';
        }
        titleBlinkTimer = setInterval(() => {
            titleBlinkState = !titleBlinkState;
            document.title = titleBlinkState ? currentBlinkText : originalTitle;
        }, 1000);
    }

    function stopTitleBlink() {
        if (titleBlinkTimer) {
            clearInterval(titleBlinkTimer);
            titleBlinkTimer = null;
        }
        if (originalTitle) {
            document.title = originalTitle;
        }
    }

    // =========================================================================
    // BUZZER LOOP & EMERGENCY ALERT CONTROL
    // =========================================================================

    function startBuzzer(escalationsList = []) {
        isBuzzerRunning = true;
        if (!buzzerInterval) {
            playBuzzerTone();
            buzzerInterval = setInterval(playBuzzerTone, 5000);
        }

        // Start title emergency blinking
        startTitleBlink('🚨 (1) URGENT ESCALATION!');

        // Update emergency banner
        updateEmergencyBanner(true, escalationsList);

        // Update navbar buzzer button
        updateBuzzerButtonUi();

        // Fire desktop notification for newly detected escalations
        if (Array.isArray(escalationsList)) {
            for (const esc of escalationsList) {
                const phone = esc.phone || esc.formatted_phone || '';
                if (phone && !notifiedEscalationPhones.has(phone)) {
                    notifiedEscalationPhones.add(phone);
                    const name = esc.contact_name || esc.display_name || phone;
                    const reason = esc.escalation_reason || esc.reason_code || 'Customer requested human agent';
                    fireDesktopNotification(
                        '🚨 Urgent: WhatsApp Customer Escalation',
                        `${name}: ${reason}. Automated bot paused. Click to open Live Chat.`,
                        'esc-' + phone,
                        `${baseUrl}/index.php?page=live-chat&phone=${encodeURIComponent(phone)}`
                    );
                }
            }
        }
    }

    function stopBuzzer() {
        isBuzzerRunning = false;
        if (buzzerInterval) {
            clearInterval(buzzerInterval);
            buzzerInterval = null;
        }
        stopTitleBlink();
        updateEmergencyBanner(false);
        updateBuzzerButtonUi();
    }

    function toggleMute() {
        isBuzzerMuted = !isBuzzerMuted;
        localStorage.setItem('whats_buzzer_muted', isBuzzerMuted ? '1' : '0');
        updateBuzzerButtonUi();

        if (!isBuzzerMuted && isBuzzerRunning) {
            playBuzzerTone();
        }
        return isBuzzerMuted;
    }

    // =========================================================================
    // UI UPDATES (Navbar, Banner, Sidebar)
    // =========================================================================

    function updateBuzzerButtonUi() {
        const btn = document.getElementById('globalBuzzerBtn');
        const icon = document.getElementById('globalBuzzerIcon');
        const label = document.getElementById('globalBuzzerLabel');
        const badge = document.getElementById('globalBuzzerBadge');

        if (btn && icon) {
            if (isBuzzerMuted) {
                icon.textContent = '🔕';
                if (label) label.textContent = 'Muted';
                btn.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-semibold rounded-lg border border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100 transition-all shadow-subtle btn-tactile';
            } else {
                icon.textContent = '🔔';
                if (label) label.textContent = 'Buzzer';
                if (isBuzzerRunning) {
                    btn.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-bold rounded-lg border border-rose-500 bg-rose-600 text-white animate-pulse transition-all shadow-md btn-tactile';
                } else {
                    btn.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-semibold rounded-lg border border-slate-200/90 bg-white hover:bg-slate-50 text-slate-700 transition-all shadow-subtle btn-tactile';
                }
            }

            if (badge) {
                if (isBuzzerRunning) {
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            }
        }

        // Also update live-chat buzzer button if present on page
        const lcBtn = document.getElementById('btnBuzzerControl');
        const lcIcon = document.getElementById('buzzerIcon');
        const lcLabel = document.getElementById('buzzerLabel');
        if (lcBtn && lcIcon) {
            if (isBuzzerMuted) {
                lcIcon.textContent = '🔕';
                if (lcLabel) lcLabel.textContent = 'Muted';
                lcBtn.className = 'inline-flex items-center gap-1 px-2 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg text-xs font-semibold border border-rose-200 transition-all';
            } else {
                lcIcon.textContent = '🔔';
                if (lcLabel) lcLabel.textContent = 'Buzzer';
                lcBtn.className = isBuzzerRunning
                    ? 'inline-flex items-center gap-1 px-2 py-1 bg-rose-600 text-white rounded-lg text-xs font-bold border border-rose-700 animate-pulse transition-all'
                    : 'inline-flex items-center gap-1 px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold border border-slate-200 transition-all';
            }
        }
    }

    function updateNotificationPermissionUi() {
        const notifBtn = document.getElementById('globalNotifBtn');
        if (!notifBtn) return;
        if ('Notification' in window && Notification.permission === 'default') {
            notifBtn.classList.remove('hidden');
        } else {
            notifBtn.classList.add('hidden');
        }
    }

    function updateEmergencyBanner(show, escalationsList = []) {
        const banner = document.getElementById('globalEscalationBanner');
        if (!banner) return;

        if (show) {
            banner.classList.remove('hidden');
            const custEl = document.getElementById('globalEscalationCustomer');
            const reasonEl = document.getElementById('globalEscalationReason');
            const openLink = document.getElementById('globalEscalationOpenChatBtn');

            if (escalationsList && escalationsList.length > 0) {
                const first = escalationsList[0];
                const phone = first.phone || first.formatted_phone || '';
                const name = first.contact_name || first.display_name || phone;
                const reason = first.escalation_reason || first.reason_code || 'Customer requested human takeover.';

                if (custEl) custEl.textContent = name;
                if (reasonEl) reasonEl.textContent = reason;
                if (openLink && phone) {
                    openLink.href = `${baseUrl}/index.php?page=live-chat&phone=${encodeURIComponent(phone)}`;
                }
            }
        } else {
            banner.classList.add('hidden');
        }
    }

    function updateSidebarUnreadCount(totalUnread) {
        const badge = document.getElementById('sidebarLiveChatBadge');
        if (!badge) return;

        if (totalUnread > 0) {
            badge.textContent = String(totalUnread);
            badge.className = 'px-1.5 py-0.2 rounded-full bg-emerald-500 text-white text-[10px] font-bold font-mono shadow-xs animate-pulse';
        } else {
            badge.textContent = 'Live';
            badge.className = 'px-1.5 py-0.2 rounded bg-emerald-50 text-emerald-700 text-[10px] font-semibold border border-emerald-200/60 font-mono';
        }
    }

    // Acknowledge all active escalations to silence the alarm
    async function acknowledgeAllEscalations() {
        try {
            const res = await fetch(`${apiBase}/escalations.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ action: 'acknowledge' })
            });
            const data = await res.json();
            if (data.ok) {
                stopBuzzer();
                activeEscalations = [];
            }
        } catch (err) {
            console.warn('[WhatsAlerts Acknowledge Error]:', err);
            stopBuzzer();
        }
    }

    // =========================================================================
    // BACKGROUND POLLER (Runs continuously across all dashboard pages)
    // =========================================================================

    async function pollAlerts() {
        // If on live-chat page, live-chat.js already polls conversations.php;
        // but we still ensure escalations are checked in case live-chat.js paused
        try {
            const res = await fetch(`${apiBase}/conversations.php?limit=15`, {
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) return;
            const data = await res.json();
            if (!data.ok) return;

            const conversations = data.conversations || [];
            const activeEscCount = Number(data.active_escalations_count || 0);

            // 1. Check Escalations
            const escalatedConvs = conversations.filter(c => c.status === 'escalated');
            if (activeEscCount > 0 || escalatedConvs.length > 0) {
                startBuzzer(escalatedConvs);
            } else {
                if (isBuzzerRunning) {
                    stopBuzzer();
                }
            }

            // 2. Calculate Total Unread Messages
            let totalUnread = 0;
            let hasNewIncomingMessage = false;
            let newestMessageConv = null;

            for (const conv of conversations) {
                const unread = Number(conv.unread_count || 0);
                totalUnread += unread;

                // Check for new message arrival
                const phone = conv.phone;
                const lastAt = conv.last_message_at || '';
                const prevAt = knownMessageTimestamps.get(phone);

                if (prevAt && lastAt && lastAt > prevAt && unread > 0) {
                    hasNewIncomingMessage = true;
                    newestMessageConv = conv;
                }
                if (lastAt) {
                    knownMessageTimestamps.set(phone, lastAt);
                }
            }

            // Update sidebar Live Chat unread count badge
            updateSidebarUnreadCount(totalUnread);

            // 3. New message alert (if window is in background or not on live-chat)
            if (hasNewIncomingMessage && newestMessageConv) {
                playMessageChime();

                // If document is in background / minimized, fire desktop notification & title ping
                if (document.hidden || !isLiveChatPage()) {
                    const contact = newestMessageConv.contact_name || newestMessageConv.display_name || newestMessageConv.phone;
                    const preview = newestMessageConv.last_message || 'New WhatsApp message';
                    fireDesktopNotification(
                        `💬 ${contact}`,
                        preview,
                        'msg-' + newestMessageConv.phone,
                        `${baseUrl}/index.php?page=live-chat&phone=${encodeURIComponent(newestMessageConv.phone)}`
                    );

                    if (!isBuzzerRunning) {
                        startTitleBlink(`🔔 New message from ${contact}`);
                        setTimeout(stopTitleBlink, 8000);
                    }
                }
            }

            lastKnownUnreadTotal = totalUnread;

        } catch (err) {
            // Network failure or offline
        }
    }

    // =========================================================================
    // INITIALIZATION & EVENT LISTENERS
    // =========================================================================

    function init() {
        updateBuzzerButtonUi();
        updateNotificationPermissionUi();

        // Navbar Buzzer Control click listener
        const globalBuzzerBtn = document.getElementById('globalBuzzerBtn');
        if (globalBuzzerBtn) {
            globalBuzzerBtn.addEventListener('click', (e) => {
                e.preventDefault();
                unlockAudioOnGesture();
                toggleMute();
            });
        }

        // Navbar Notification Enable click listener
        const globalNotifBtn = document.getElementById('globalNotifBtn');
        if (globalNotifBtn) {
            globalNotifBtn.addEventListener('click', async (e) => {
                e.preventDefault();
                unlockAudioOnGesture();
                const perm = await requestDesktopNotifications();
                if (perm === 'granted') {
                    playMessageChime();
                    fireDesktopNotification('✓ Alerts Active', 'You will receive instant desktop notifications for incoming messages and customer escalations.', 'welcome-alert');
                }
            });
        }

        // Global Silence Alarm button in emergency banner
        const silenceBtn = document.getElementById('globalEscalationSilenceBtn');
        if (silenceBtn) {
            silenceBtn.addEventListener('click', (e) => {
                e.preventDefault();
                acknowledgeAllEscalations();
            });
        }

        // Listen for storage changes across tabs (e.g. muted in another tab)
        window.addEventListener('storage', (e) => {
            if (e.key === 'whats_buzzer_muted') {
                isBuzzerMuted = e.newValue === '1';
                updateBuzzerButtonUi();
                if (isBuzzerMuted && buzzerInterval) {
                    clearInterval(buzzerInterval);
                    buzzerInterval = null;
                } else if (!isBuzzerMuted && isBuzzerRunning && !buzzerInterval) {
                    buzzerInterval = setInterval(playBuzzerTone, 5000);
                }
            }
        });

        // Run immediate poll, then schedule recurring poll every 4 seconds
        pollAlerts();
        pollTimer = setInterval(pollAlerts, 4000);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Public API exposed on window.WhatsAlerts
    window.WhatsAlerts = {
        initAudioContext,
        playBuzzerTone,
        playMessageChime,
        startBuzzer,
        stopBuzzer,
        toggleMute,
        isMuted: () => isBuzzerMuted,
        isRunning: () => isBuzzerRunning,
        requestDesktopNotifications,
        fireDesktopNotification,
        acknowledgeAll: acknowledgeAllEscalations,
        syncEscalations: function (activeCount, conversations) {
            if (activeCount > 0) {
                const escalated = (conversations || []).filter(c => c.status === 'escalated');
                startBuzzer(escalated);
            } else {
                stopBuzzer();
            }
        }
    };

})(window, document);
