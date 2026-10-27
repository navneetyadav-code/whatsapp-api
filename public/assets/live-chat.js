/**
 * WhatsGateway Native WhatsApp Live Chat Studio Engine
 * Full real-time two-way messaging, smart delta polling, human takeover,
 * hardware WebAudio alarm buzzer, native desktop push notifications, and optimistic dispatch.
 */

(function () {
    'use strict';

    // 1. Resolve API Base URL cleanly across standard and nested subdirectory paths
    const basePath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/'));
    const API_BASE = `${basePath}/api/chat`;

    // 2. Application State
    const state = {
        activePhone: null,
        activeConversation: null,
        lastMessageId: 0,
        conversations: [],
        currentFilter: 'all',
        searchQuery: '',
        isSending: false,
        selectedFile: null,
        pollTimer: null,
        convTimer: null,
        activeEscalationsCount: 0,
        buzzerMuted: localStorage.getItem('whats_buzzer_muted') === '1',
    };

    // 3. Audio & Notification System State
    let audioCtx = null;
    let buzzerInterval = null;
    const notifiedEscalationIds = new Set();

    // 4. DOM Elements
    const dom = {
        conversationsContainer: document.getElementById('conversationsContainer'),
        chatSearchInput: document.getElementById('chatSearchInput'),
        chatSearchClear: document.getElementById('chatSearchClear'),
        unreadFilterBadge: document.getElementById('unreadFilterBadge'),
        escalatedFilterBadge: document.getElementById('escalatedFilterBadge'),
        btnRefreshConversations: document.getElementById('btnRefreshConversations'),
        filterBtns: document.querySelectorAll('.chat-filter-btn'),

        btnBuzzerControl: document.getElementById('btnBuzzerControl'),
        buzzerIcon: document.getElementById('buzzerIcon'),
        buzzerLabel: document.getElementById('buzzerLabel'),

        noChatSelected: document.getElementById('noChatSelected'),
        activeChatWrapper: document.getElementById('activeChatWrapper'),
        chatActiveAvatar: document.getElementById('chatActiveAvatar'),
        chatActiveName: document.getElementById('chatActiveName'),
        chatActivePhone: document.getElementById('chatActivePhone'),
        chatActiveBotBadge: document.getElementById('chatActiveBotBadge'),
        chatActiveEscalatedBadge: document.getElementById('chatActiveEscalatedBadge'),
        btnManualEscalate: document.getElementById('btnManualEscalate'),
        btnToggleBot: document.getElementById('btnToggleBot'),
        botToggleIcon: document.getElementById('botToggleIcon'),
        botToggleLabel: document.getElementById('botToggleLabel'),
        btnCloseChat: document.getElementById('btnCloseChat'),
        closeChatLabel: document.getElementById('closeChatLabel'),

        chatEscalationBanner: document.getElementById('chatEscalationBanner'),
        escalationReasonText: document.getElementById('escalationReasonText'),
        btnAcknowledgeEscalation: document.getElementById('btnAcknowledgeEscalation'),
        btnResolveEscalation: document.getElementById('btnResolveEscalation'),

        chatClosedBanner: document.getElementById('chatClosedBanner'),
        btnReopenBanner: document.getElementById('btnReopenBanner'),

        messagesContainer: document.getElementById('messagesContainer'),
        chatSendForm: document.getElementById('chatSendForm'),
        chatMessageInput: document.getElementById('chatMessageInput'),
        chatFileInput: document.getElementById('chatFileInput'),
        btnAttachFile: document.getElementById('btnAttachFile'),
        attachmentPreview: document.getElementById('attachmentPreview'),
        attachmentFileName: document.getElementById('attachmentFileName'),
        btnRemoveAttachment: document.getElementById('btnRemoveAttachment'),
        btnSendMessage: document.getElementById('btnSendMessage'),

        btnNewChat: document.getElementById('btnNewChat'),
        newChatModal: document.getElementById('newChatModal'),
        btnCloseNewChatModal: document.getElementById('btnCloseNewChatModal'),
        btnCancelNewChat: document.getElementById('btnCancelNewChat'),
        newChatForm: document.getElementById('newChatForm'),
        newChatPhone: document.getElementById('newChatPhone'),
        newChatName: document.getElementById('newChatName'),
    };

    // Exit early if live-chat markup is not present on the current page
    if (!dom.conversationsContainer) return;

    // Helper: Escapes HTML to prevent XSS
    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // Helper: Canonical phone normalizer (defaults 10-digit numbers to '91')
    function normalizePhone(raw) {
        let digits = String(raw || '').replace(/\D/g, '');
        if (digits.startsWith('00')) {
            digits = digits.slice(2);
        }
        if (digits.length === 11 && digits.startsWith('0')) {
            digits = '91' + digits.slice(1);
        }
        if (digits.length === 10) {
            digits = '91' + digits;
        }
        return digits;
    }

    // Helper: Pretty formats a canonical phone number
    function formatPhone(phone) {
        const clean = String(phone || '').replace(/\D/g, '');
        if (clean.length === 12 && clean.startsWith('91')) {
            return `+91 ${clean.slice(2, 7)} ${clean.slice(7)}`;
        }
        return clean ? `+${clean}` : '';
    }

    // Helper: Extracts initials from contact name or phone
    function getInitials(name, phone) {
        if (name && name.trim()) {
            const parts = name.trim().split(/\s+/);
            if (parts.length >= 2) {
                return (parts[0][0] + parts[1][0]).toUpperCase();
            }
            return name.substring(0, 2).toUpperCase();
        }
        if (phone) {
            const clean = phone.replace(/\D/g, '');
            return clean.slice(-2);
        }
        return 'WA';
    }

    // =========================================================================
    // WEBAUDIO BUZZER & DESKTOP PUSH NOTIFICATION SYSTEM
    // =========================================================================

    function initAudioContext() {
        if (!audioCtx) {
            const AudioContextClass = window.AudioContext || window.webkitAudioContext;
            if (AudioContextClass) {
                audioCtx = new AudioContextClass();
            }
        }
        if (audioCtx && audioCtx.state === 'suspended') {
            audioCtx.resume();
        }
    }

    /**
     * Synthesizes hardware alert buzzer: 880Hz -> 1320Hz oscillating tone with gain envelope
     */
    function playBuzzerTone() {
        if (state.buzzerMuted) return;
        try {
            initAudioContext();
            if (!audioCtx || audioCtx.state !== 'running') return;

            const now = audioCtx.currentTime;
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();

            osc.type = 'square';
            // Dual-tone frequency jump: 880Hz (A5) -> 1320Hz (E6)
            osc.frequency.setValueAtTime(880, now);
            osc.frequency.setValueAtTime(1320, now + 0.16);

            // Envelope: ramp up and smooth decay to prevent harsh audio clicks
            gain.gain.setValueAtTime(0.001, now);
            gain.gain.exponentialRampToValueAtTime(0.35, now + 0.05);
            gain.gain.exponentialRampToValueAtTime(0.001, now + 0.38);

            osc.connect(gain);
            gain.connect(audioCtx.destination);

            osc.start(now);
            osc.stop(now + 0.40);
        } catch (e) {
            console.warn('[WebAudio Buzzer Error]:', e);
        }
    }

    function startBuzzerLoop() {
        if (buzzerInterval) return;
        playBuzzerTone();
        buzzerInterval = setInterval(() => {
            playBuzzerTone();
        }, 5500);
    }

    function stopBuzzerLoop() {
        if (buzzerInterval) {
            clearInterval(buzzerInterval);
            buzzerInterval = null;
        }
    }

    function updateBuzzerButtonUi() {
        if (!dom.btnBuzzerControl) return;
        if (state.buzzerMuted) {
            dom.buzzerIcon.textContent = '🔕';
            dom.buzzerLabel.textContent = 'Muted';
            dom.btnBuzzerControl.className = 'inline-flex items-center gap-1 px-2 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg text-xs font-semibold border border-rose-200 transition-all';
        } else {
            dom.buzzerIcon.textContent = '🔔';
            dom.buzzerLabel.textContent = 'Buzzer';
            dom.btnBuzzerControl.className = 'inline-flex items-center gap-1 px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold border border-slate-200 transition-all';
        }
    }

    function requestDesktopNotifications() {
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission().then(perm => {
                console.log('[Push Notification Permission]:', perm);
            });
        }
    }

    function fireDesktopNotification(title, body, phone) {
        if (!('Notification' in window) || Notification.permission !== 'granted') return;
        try {
            const notif = new Notification(title, {
                body: body,
                tag: 'escalation-' + phone,
                requireInteraction: true
            });
            notif.onclick = function () {
                window.focus();
                if (phone) selectConversation(phone);
                notif.close();
            };
        } catch (err) {
            console.warn('[Desktop Notification Error]:', err);
        }
    }

    // Helper: SVG checkmark status icons (WhatsApp Web style)
    function getStatusSvg(status) {
        if (status === 'read') {
            return `<span class="inline-flex text-[#53bdeb]" title="Read / Seen">
                <svg class="w-3.5 h-3.5" viewBox="0 0 16 15" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M1 8.5l3.5 3.5 7.5-7.5m-5 7.5l1.5 1.5 6.5-6.5"/>
                </svg>
            </span>`;
        }
        if (status === 'delivered') {
            return `<span class="inline-flex text-slate-400" title="Delivered">
                <svg class="w-3.5 h-3.5" viewBox="0 0 16 15" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M1 8.5l3.5 3.5 7.5-7.5m-5 7.5l1.5 1.5 6.5-6.5"/>
                </svg>
            </span>`;
        }
        if (status === 'sent') {
            return `<span class="inline-flex text-slate-400" title="Sent">
                <svg class="w-3.5 h-3.5" viewBox="0 0 16 15" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.5 8.5l4 4 8-8"/>
                </svg>
            </span>`;
        }
        if (status === 'failed') {
            return `<span class="inline-flex text-rose-500" title="Failed">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
            </span>`;
        }
        return `<span class="inline-flex text-slate-400" title="Sending...">
            <svg class="w-3 h-3 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
        </span>`;
    }

    // =========================================================================
    // CONVERSATIONS LIST
    // =========================================================================

    async function fetchConversations(silent = false) {
        try {
            const url = `${API_BASE}/conversations.php?q=${encodeURIComponent(state.searchQuery)}`;
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();
            if (!data.ok) throw new Error(data.error || 'Failed to load conversations');

            state.conversations = data.conversations || [];
            state.activeEscalationsCount = Number(data.active_escalations_count || 0);

            // Manage Buzzer Alarm & Desktop Notification Triggers
            if (state.activeEscalationsCount > 0) {
                startBuzzerLoop();
                if (dom.escalatedFilterBadge) {
                    dom.escalatedFilterBadge.textContent = `(${state.activeEscalationsCount})`;
                }

                // Check for newly escalated conversations to notify
                for (const c of state.conversations) {
                    if (c.status === 'escalated' && !notifiedEscalationIds.has(c.phone)) {
                        notifiedEscalationIds.add(c.phone);
                        const contactName = c.contact_name || c.formatted_phone || c.phone;
                        const reason = c.escalation_reason || 'Customer requested human agent';
                        fireDesktopNotification(
                            '🚨 Urgent: WhatsApp Customer Escalation',
                            `${contactName}: ${reason}. Automated bot paused.`,
                            c.phone
                        );
                    }
                }
            } else {
                stopBuzzerLoop();
                if (dom.escalatedFilterBadge) {
                    dom.escalatedFilterBadge.textContent = '';
                }
            }

            renderConversations();
            updateUnreadBadge();

            // Refresh active chat UI in case status changed (e.g. escalated)
            if (state.activePhone) {
                const current = state.conversations.find(c => c.phone === state.activePhone);
                if (current) {
                    state.activeConversation = current;
                    updateBotToggleUi(current.bot_paused);
                    updateChatClosedUi(current);
                    updateEscalationUi(current);
                }
            }
        } catch (err) {
            if (!silent) {
                dom.conversationsContainer.innerHTML = `
                    <div class="p-6 text-center text-rose-500 text-xs">
                        <p class="font-bold mb-1">Failed to load conversations</p>
                        <p class="text-slate-400 text-[11px]">${escapeHtml(err.message)}</p>
                    </div>`;
            }
        }
    }

    function renderConversations() {
        let items = state.conversations;

        if (state.currentFilter === 'unread') {
            items = items.filter(c => c.unread_count > 0);
        } else if (state.currentFilter === 'closed') {
            items = items.filter(c => c.status === 'closed');
        } else if (state.currentFilter === 'escalated') {
            items = items.filter(c => c.status === 'escalated');
        }

        if (items.length === 0) {
            dom.conversationsContainer.innerHTML = `
                <div class="p-8 text-center text-slate-400 select-none">
                    <div class="w-10 h-10 rounded-full bg-slate-100 mx-auto mb-2 flex items-center justify-center text-slate-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                    </div>
                    <p class="text-xs font-semibold text-slate-500 mb-0.5">No conversations</p>
                    <p class="text-[11px] text-slate-400">Inbound messages or new chats will appear here.</p>
                </div>`;
            return;
        }

        const html = items.map(c => {
            const isSelected = state.activePhone === c.phone;
            const initials = getInitials(c.contact_name, c.phone);
            const isBotPaused = c.bot_paused === 1;
            const isClosed = c.status === 'closed';
            const isEscalated = c.status === 'escalated';
            const formatted = c.formatted_phone || formatPhone(c.phone);

            let borderStyle = 'hover:bg-slate-50';
            if (isEscalated) {
                borderStyle = isSelected ? 'bg-rose-50/80 border-l-4 border-rose-600' : 'bg-rose-50/30 border-l-3 border-rose-500';
            } else if (isSelected) {
                borderStyle = 'bg-emerald-50/60 border-l-3 border-emerald-600';
            }

            return `
                <div class="conv-item p-3 transition-all cursor-pointer flex items-center gap-3 relative select-none ${borderStyle}" data-phone="${escapeHtml(c.phone)}" data-name="${escapeHtml(c.contact_name || '')}">
                    <div class="w-10 h-10 rounded-full ${isEscalated ? 'bg-rose-700 text-white animate-pulse' : (isSelected ? 'bg-emerald-700 text-white' : 'bg-slate-800 text-white')} flex items-center justify-center font-bold text-xs font-mono shrink-0 shadow-2xs">
                        ${escapeHtml(initials)}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-0.5">
                            <h4 class="text-xs font-bold ${isEscalated ? 'text-rose-950 font-black' : 'text-slate-900'} truncate">${escapeHtml(c.display_name || formatted)}</h4>
                            <span class="text-[10px] text-slate-400 font-mono shrink-0 ml-1">${escapeHtml(c.time_ago)}</span>
                        </div>
                        <div class="flex items-center justify-between gap-1">
                            <p class="text-[11px] ${isEscalated ? 'text-rose-700 font-semibold' : 'text-slate-500'} truncate font-medium flex-1">${escapeHtml(c.last_message || 'No messages')}</p>
                            <div class="flex items-center gap-1 shrink-0">
                                ${isEscalated ? '<span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-rose-600 text-white shadow-2xs">🚨 ESCALATED</span>' : ''}
                                ${isClosed ? '<span class="px-1 py-0.2 rounded text-[9px] font-bold bg-slate-100 text-slate-600 border border-slate-200">Closed</span>' : ''}
                                ${isBotPaused && !isClosed && !isEscalated ? '<span class="px-1 py-0.2 rounded text-[9px] font-bold bg-amber-50 text-amber-700 border border-amber-200">⏸️ Takeover</span>' : ''}
                                ${c.unread_count > 0 ? `<span class="px-1.5 py-0.2 rounded-full bg-emerald-500 text-white text-[10px] font-bold font-mono shadow-2xs">${c.unread_count}</span>` : ''}
                            </div>
                        </div>
                    </div>
                </div>`;
        }).join('');

        dom.conversationsContainer.innerHTML = html;

        // Attach click listeners to cards
        dom.conversationsContainer.querySelectorAll('.conv-item').forEach(item => {
            item.addEventListener('click', () => {
                const phone = item.getAttribute('data-phone');
                const name = item.getAttribute('data-name');
                selectConversation(phone, name);
            });
        });
    }

    function updateUnreadBadge() {
        const total = state.conversations.reduce((acc, c) => acc + (c.unread_count || 0), 0);
        if (dom.unreadFilterBadge) {
            dom.unreadFilterBadge.textContent = total > 0 ? `(${total})` : '';
            dom.unreadFilterBadge.className = total > 0 ? 'ml-0.5 text-[9px] font-mono text-emerald-700 font-bold' : 'ml-0.5 text-[9px] font-mono text-slate-400';
        }
    }

    // =========================================================================
    // ACTIVE CONVERSATION & MESSAGES
    // =========================================================================

    async function selectConversation(phone, name = null) {
        if (!phone) return;
        const normalized = normalizePhone(phone);
        state.activePhone = normalized;
        state.lastMessageId = 0;

        // Unlock audio context on user gesture
        initAudioContext();

        // Switch panels
        dom.noChatSelected.classList.add('hidden');
        dom.activeChatWrapper.classList.remove('hidden');

        // Find conversation details
        const conv = state.conversations.find(c => c.phone === normalized);
        const formatted = formatPhone(normalized);
        const displayName = conv ? conv.display_name : (name ? name : formatted);
        state.activeConversation = conv;
        if (conv && conv.unread_count > 0) {
            conv.unread_count = 0;
            updateUnreadBadge();
        }

        // Update Header
        dom.chatActiveName.textContent = displayName;
        dom.chatActivePhone.textContent = formatted;
        dom.chatActiveAvatar.textContent = getInitials(displayName, normalized);
        updateBotToggleUi(conv ? conv.bot_paused : 0);
        updateChatClosedUi(conv);
        updateEscalationUi(conv);

        // Highlight active item in list
        renderConversations();

        // Clear and show loading in messages feed
        dom.messagesContainer.innerHTML = `
            <div class="h-full flex items-center justify-center text-slate-400 text-xs py-12">
                <svg class="w-6 h-6 animate-spin text-slate-300 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                <span>Loading messages...</span>
            </div>`;

        await fetchMessages(normalized, false);

        // Focus text input
        dom.chatMessageInput.focus();

        // Start real-time delta polling for active chat
        restartMessagePolling();
    }

    function updateBotToggleUi(botPaused) {
        const isPaused = Number(botPaused) === 1;
        if (isPaused) {
            dom.btnToggleBot.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all active:scale-[0.98] border-amber-300 text-amber-800 bg-amber-50 hover:bg-amber-100 shadow-2xs';
            dom.botToggleIcon.textContent = '⏸️';
            dom.botToggleLabel.textContent = 'Human Takeover';
            dom.btnToggleBot.title = 'Bot is paused. Click to resume automated AI replies.';
            dom.chatActiveBotBadge.className = 'px-1.5 py-0.2 rounded text-[10px] font-bold font-mono bg-amber-100 text-amber-800 border border-amber-200';
            dom.chatActiveBotBadge.textContent = 'Takeover';
        } else {
            dom.btnToggleBot.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all active:scale-[0.98] border-emerald-200 text-emerald-800 bg-emerald-50 hover:bg-emerald-100 shadow-2xs';
            dom.botToggleIcon.textContent = '🤖';
            dom.botToggleLabel.textContent = 'Bot Active';
            dom.btnToggleBot.title = 'Bot is active. Click to pause bot and take over manually.';
            dom.chatActiveBotBadge.className = 'px-1.5 py-0.2 rounded text-[10px] font-bold font-mono bg-emerald-100 text-emerald-800 border border-emerald-200';
            dom.chatActiveBotBadge.textContent = 'Bot Active';
        }
    }

    function updateChatClosedUi(conv) {
        const isClosed = conv && conv.status === 'closed';
        if (isClosed) {
            dom.chatClosedBanner.classList.remove('hidden');
            dom.btnCloseChat.classList.add('hidden');
        } else {
            dom.chatClosedBanner.classList.add('hidden');
            dom.btnCloseChat.classList.remove('hidden');
        }
    }

    function updateEscalationUi(conv) {
        const isEscalated = conv && conv.status === 'escalated';
        if (isEscalated) {
            dom.chatEscalationBanner.classList.remove('hidden');
            dom.chatActiveEscalatedBadge.classList.remove('hidden');
            dom.escalationReasonText.textContent = conv.escalation_reason || 'Customer requested human agent';
        } else {
            dom.chatEscalationBanner.classList.add('hidden');
            dom.chatActiveEscalatedBadge.classList.add('hidden');
        }
    }

    async function handleToggleBot() {
        if (!state.activePhone) return;
        try {
            const res = await fetch(`${API_BASE}/toggle-bot.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ phone: state.activePhone })
            });
            const data = await res.json();
            if (!data.ok) throw new Error(data.error || 'Failed to toggle bot');

            updateBotToggleUi(data.bot_paused);
            const conv = state.conversations.find(c => c.phone === state.activePhone);
            if (conv) {
                conv.bot_paused = data.bot_paused;
                if (data.bot_paused === 0) {
                    conv.status = 'open';
                    conv.escalation_reason = null;
                }
                updateEscalationUi(conv);
                renderConversations();
            }
        } catch (err) {
            alert('Could not toggle bot: ' + err.message);
        }
    }

    async function handleAcknowledgeEscalation() {
        if (!state.activePhone) return;
        try {
            const res = await fetch(`${API_BASE}/escalations.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ action: 'acknowledge', phone: state.activePhone })
            });
            const data = await res.json();
            if (!data.ok) throw new Error(data.error || 'Failed to acknowledge');

            stopBuzzerLoop();
            fetchConversations(true);
        } catch (err) {
            alert('Error: ' + err.message);
        }
    }

    async function handleResolveEscalation() {
        if (!state.activePhone) return;
        try {
            const res = await fetch(`${API_BASE}/escalations.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ action: 'resolve', phone: state.activePhone })
            });
            const data = await res.json();
            if (!data.ok) throw new Error(data.error || 'Failed to resolve');

            stopBuzzerLoop();
            dom.chatEscalationBanner.classList.add('hidden');
            dom.chatActiveEscalatedBadge.classList.add('hidden');
            const conv = state.conversations.find(c => c.phone === state.activePhone);
            if (conv) {
                conv.status = 'open';
                conv.escalation_reason = null;
            }
            fetchConversations(true);
        } catch (err) {
            alert('Error: ' + err.message);
        }
    }

    async function handleManualEscalate() {
        if (!state.activePhone) return;
        const reason = prompt('Enter reason for escalating this conversation:', 'Agent requested priority attention');
        if (reason === null) return;

        try {
            const res = await fetch(`${API_BASE}/escalations.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ action: 'escalate_manually', phone: state.activePhone, reason: reason || 'manual_agent_escalation' })
            });
            const data = await res.json();
            if (!data.ok) throw new Error(data.error || 'Failed to escalate');

            fetchConversations(true);
        } catch (err) {
            alert('Error: ' + err.message);
        }
    }

    async function handleCloseOrReopenChat(action = 'close') {
        if (!state.activePhone) return;
        const confirmMsg = action === 'close'
            ? 'Close this conversation? A 5-star customer rating feedback request will be dispatched automatically.'
            : 'Reopen this conversation?';
        if (!confirm(confirmMsg)) return;

        try {
            const res = await fetch(`${API_BASE}/close.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ phone: state.activePhone, action })
            });
            const data = await res.json();
            if (!data.ok) throw new Error(data.error || 'Failed to update conversation status');

            const conv = state.conversations.find(c => c.phone === state.activePhone);
            if (conv) {
                conv.status = data.status;
                updateChatClosedUi(conv);
                renderConversations();
            }
            fetchMessages(state.activePhone, true);
        } catch (err) {
            alert('Failed: ' + err.message);
        }
    }

    // =========================================================================
    // MESSAGES FEED & RENDERING
    // =========================================================================

    async function fetchMessages(phone, isDelta = false) {
        try {
            const url = `${API_BASE}/messages.php?phone=${encodeURIComponent(phone)}&after_id=${isDelta ? state.lastMessageId : 0}`;
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();
            if (!data.ok) throw new Error(data.error || 'Failed to load messages');

            const messages = data.messages || [];

            if (!isDelta) {
                renderAllMessages(messages);
                scrollToBottom();
            } else if (messages.length > 0) {
                appendDeltaMessages(messages);
                scrollToBottom();
            }

            if (messages.length > 0) {
                state.lastMessageId = Math.max(...messages.map(m => m.id));
            }
        } catch (err) {
            console.warn('[LiveChat Messages Fetch Error]:', err.message);
        }
    }

    function renderAllMessages(messages) {
        if (!messages || messages.length === 0) {
            dom.messagesContainer.innerHTML = `
                <div class="h-full flex flex-col items-center justify-center text-slate-400 text-xs py-12 select-none">
                    <div class="w-10 h-10 rounded-full bg-white/80 border border-slate-200/60 flex items-center justify-center text-slate-400 mb-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                    </div>
                    <p class="font-semibold text-slate-600">No message history</p>
                    <p class="text-[11px] text-slate-400">Type a message below to start the conversation.</p>
                </div>`;
            return;
        }

        const html = messages.map(msg => renderSingleMessageBubble(msg)).join('');
        dom.messagesContainer.innerHTML = html;
    }

    function appendDeltaMessages(messages) {
        messages.forEach(msg => {
            const existing = document.getElementById(`msg-${msg.id}`);
            if (!existing) {
                const bubbleHtml = renderSingleMessageBubble(msg);
                dom.messagesContainer.insertAdjacentHTML('beforeend', bubbleHtml);
            }
        });
    }

    function renderSingleMessageBubble(msg) {
        const isOutbound = msg.sender_type === 'agent' || msg.sender_type === 'bot';
        const isBot = msg.sender_type === 'bot';
        const statusSvg = isOutbound ? getStatusSvg(msg.status) : '';

        let mediaHtml = '';
        if (msg.media_url) {
            if (msg.message_type === 'image') {
                mediaHtml = `<div class="mb-1.5"><a href="${escapeHtml(msg.media_url)}" target="_blank"><img src="${escapeHtml(msg.media_url)}" alt="Photo" class="rounded-lg max-h-60 max-w-full object-cover"></a></div>`;
            } else if (msg.message_type === 'video') {
                mediaHtml = `<div class="mb-1.5"><video controls src="${escapeHtml(msg.media_url)}" class="rounded-lg max-h-60 max-w-full"></video></div>`;
            } else if (msg.message_type === 'audio') {
                mediaHtml = `<div class="mb-1.5"><audio controls src="${escapeHtml(msg.media_url)}" class="h-8 max-w-[260px]"></audio></div>`;
            } else {
                const fileName = msg.media_url.split('/').pop() || 'Attachment';
                mediaHtml = `
                    <div class="mb-1.5">
                        <a href="${escapeHtml(msg.media_url)}" target="_blank" class="inline-flex items-center gap-2 p-2 bg-black/5 rounded-lg text-xs font-mono font-medium hover:bg-black/10 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span class="truncate max-w-[180px]">${escapeHtml(fileName)}</span>
                        </a>
                    </div>`;
            }
        }

        const bodyHtml = escapeHtml(msg.body).replace(/\n/g, '<br>');

        return `
            <div id="msg-${msg.id}" class="flex ${isOutbound ? 'justify-end' : 'justify-start'}">
                <div class="max-w-[78%] md:max-w-[65%] rounded-2xl px-3.5 py-2 shadow-2xs relative select-text ${isOutbound ? (isBot ? 'bg-[#DCF8C6]/80 text-slate-900 border border-[#b8e49c]' : 'bg-[#DCF8C6] text-slate-900 border border-[#b8e49c]') : 'bg-white text-slate-900 border border-slate-200/70'}">
                    ${isBot ? '<div class="text-[9px] font-bold text-emerald-800 font-mono uppercase tracking-wider mb-0.5">🤖 AI Assistant</div>' : ''}
                    ${mediaHtml}
                    ${bodyHtml ? `<p class="text-xs font-sans leading-relaxed break-words">${bodyHtml}</p>` : ''}
                    <div class="flex items-center justify-end gap-1 mt-1 select-none">
                        <span class="text-[10px] text-slate-400 font-mono">${escapeHtml(msg.formatted_time || '')}</span>
                        ${statusSvg}
                    </div>
                </div>
            </div>`;
    }

    function scrollToBottom() {
        dom.messagesContainer.scrollTop = dom.messagesContainer.scrollHeight;
    }

    // =========================================================================
    // SENDING MESSAGES & DISPATCH
    // =========================================================================

    async function handleSendMessage() {
        if (!state.activePhone || state.isSending) return;
        const text = dom.chatMessageInput.value.trim();
        const file = state.selectedFile;

        if (!text && !file) return;

        state.isSending = true;
        dom.btnSendMessage.disabled = true;

        // Optimistic UI representation
        const optimisticId = 'opt-' + Date.now();
        const tempBubbleHtml = `
            <div id="${optimisticId}" class="flex justify-end opacity-70">
                <div class="max-w-[78%] md:max-w-[65%] rounded-2xl px-3.5 py-2 shadow-2xs relative select-text bg-[#DCF8C6] text-slate-900 border border-[#b8e49c]">
                    ${file ? `<div class="mb-1 text-[11px] font-mono text-slate-600">📎 Uploading ${escapeHtml(file.name)}...</div>` : ''}
                    <p class="text-xs font-sans leading-relaxed break-words">${escapeHtml(text).replace(/\n/g, '<br>')}</p>
                    <div class="flex items-center justify-end gap-1 mt-1">
                        <span class="text-[10px] text-slate-400 font-mono">sending...</span>
                        <svg class="w-3 h-3 animate-spin text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </div>
                </div>
            </div>`;
        dom.messagesContainer.insertAdjacentHTML('beforeend', tempBubbleHtml);
        scrollToBottom();

        // Clear input field immediately
        dom.chatMessageInput.value = '';
        dom.chatMessageInput.style.height = 'auto';
        clearAttachment();

        try {
            const formData = new FormData();
            formData.append('phone', state.activePhone);
            if (text) formData.append('message', text);
            if (file) formData.append('attachment', file);

            const res = await fetch(`${API_BASE}/send.php`, {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (!data.ok) throw new Error(data.error || 'Failed to dispatch message');

            // Remove optimistic placeholder and fetch fresh message
            const optEl = document.getElementById(optimisticId);
            if (optEl) optEl.remove();

            await fetchMessages(state.activePhone, true);
            fetchConversations(true);
        } catch (err) {
            alert('Failed to send message: ' + err.message);
            const optEl = document.getElementById(optimisticId);
            if (optEl) optEl.classList.add('bg-rose-100');
        } finally {
            state.isSending = false;
            dom.btnSendMessage.disabled = false;
            dom.chatMessageInput.focus();
        }
    }

    function handleFileSelected(e) {
        const file = e.target.files[0];
        if (!file) return;
        state.selectedFile = file;
        dom.attachmentFileName.textContent = file.name;
        dom.attachmentPreview.classList.remove('hidden');
    }

    function clearAttachment() {
        state.selectedFile = null;
        dom.chatFileInput.value = '';
        dom.attachmentPreview.classList.add('hidden');
    }

    // =========================================================================
    // REAL-TIME SMART POLLING
    // =========================================================================

    function restartMessagePolling() {
        if (state.pollTimer) clearInterval(state.pollTimer);
        state.pollTimer = setInterval(() => {
            if (state.activePhone) {
                fetchMessages(state.activePhone, true);
            }
        }, 2000);
    }

    function startConversationPolling() {
        if (state.convTimer) clearInterval(state.convTimer);
        // Poll every 3.5s; continue running in background for buzzer and notifications
        state.convTimer = setInterval(() => {
            fetchConversations(true);
        }, 3500);
    }

    // =========================================================================
    // EVENT LISTENERS
    // =========================================================================

    // Send on click / form submit
    dom.chatSendForm.addEventListener('submit', (e) => {
        e.preventDefault();
        handleSendMessage();
    });

    // Enter to send, Shift+Enter for newline
    dom.chatMessageInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            handleSendMessage();
        }
    });

    // Auto-expand textarea height
    dom.chatMessageInput.addEventListener('input', () => {
        dom.chatMessageInput.style.height = 'auto';
        dom.chatMessageInput.style.height = Math.min(128, dom.chatMessageInput.scrollHeight) + 'px';
    });

    // Attachment handlers
    dom.btnAttachFile.addEventListener('click', () => dom.chatFileInput.click());
    dom.chatFileInput.addEventListener('change', handleFileSelected);
    dom.btnRemoveAttachment.addEventListener('click', clearAttachment);

    // Buzzer control button (toggle mute & test tone / permissions)
    if (dom.btnBuzzerControl) {
        dom.btnBuzzerControl.addEventListener('click', () => {
            initAudioContext();
            requestDesktopNotifications();

            state.buzzerMuted = !state.buzzerMuted;
            localStorage.setItem('whats_buzzer_muted', state.buzzerMuted ? '1' : '0');
            updateBuzzerButtonUi();

            if (!state.buzzerMuted) {
                playBuzzerTone();
            } else {
                stopBuzzerLoop();
            }
        });
    }

    // Bot toggle
    dom.btnToggleBot.addEventListener('click', handleToggleBot);

    // Escalation actions
    if (dom.btnAcknowledgeEscalation) {
        dom.btnAcknowledgeEscalation.addEventListener('click', handleAcknowledgeEscalation);
    }
    if (dom.btnResolveEscalation) {
        dom.btnResolveEscalation.addEventListener('click', handleResolveEscalation);
    }
    if (dom.btnManualEscalate) {
        dom.btnManualEscalate.addEventListener('click', handleManualEscalate);
    }

    // Close / Reopen Chat
    if (dom.btnCloseChat) {
        dom.btnCloseChat.addEventListener('click', () => handleCloseOrReopenChat());
    }
    if (dom.btnReopenBanner) {
        dom.btnReopenBanner.addEventListener('click', () => handleCloseOrReopenChat('reopen'));
    }

    // Search input debounced
    let searchTimeout = null;
    dom.chatSearchInput.addEventListener('input', (e) => {
        state.searchQuery = e.target.value.trim();
        dom.chatSearchClear.classList.toggle('hidden', state.searchQuery === '');
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => fetchConversations(false), 250);
    });

    dom.chatSearchClear.addEventListener('click', () => {
        dom.chatSearchInput.value = '';
        state.searchQuery = '';
        dom.chatSearchClear.classList.add('hidden');
        fetchConversations(false);
    });

    // Filter buttons (All / Escalated / Unread / Closed)
    dom.filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            dom.filterBtns.forEach(b => {
                b.className = 'chat-filter-btn px-2.5 py-0.5 rounded-md hover:bg-slate-200/70 text-slate-600 transition-all font-semibold shrink-0';
            });
            btn.className = 'chat-filter-btn px-2.5 py-0.5 rounded-md bg-slate-900 text-white font-bold transition-all shadow-2xs shrink-0';
            state.currentFilter = btn.getAttribute('data-filter') || 'all';
            renderConversations();
        });
    });

    dom.btnRefreshConversations.addEventListener('click', () => fetchConversations(false));

    // New Chat Modal
    dom.btnNewChat.addEventListener('click', () => {
        dom.newChatModal.classList.remove('hidden');
        if (!dom.newChatPhone.value || dom.newChatPhone.value.trim() === '') {
            dom.newChatPhone.value = '91';
        }
        dom.newChatPhone.focus();
        const len = dom.newChatPhone.value.length;
        dom.newChatPhone.setSelectionRange(len, len);
    });

    function closeNewChatModal() {
        dom.newChatModal.classList.add('hidden');
        dom.newChatPhone.value = '91';
        dom.newChatName.value = '';
    }

    dom.btnCloseNewChatModal.addEventListener('click', closeNewChatModal);
    dom.btnCancelNewChat.addEventListener('click', closeNewChatModal);

    dom.newChatForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const raw = dom.newChatPhone.value.trim();
        const name = dom.newChatName.value.trim();
        const clean = normalizePhone(raw);
        if (!clean || clean.length < 10) {
            alert('Please enter a valid phone number (at least 10 digits).');
            return;
        }
        closeNewChatModal();
        selectConversation(clean, name || null);
    });

    // Keyboard Esc shortcut
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !dom.newChatModal.classList.contains('hidden')) {
            closeNewChatModal();
        }
    });

    // =========================================================================
    // INITIALIZATION
    // =========================================================================
    updateBuzzerButtonUi();
    fetchConversations(false);
    startConversationPolling();

})();
