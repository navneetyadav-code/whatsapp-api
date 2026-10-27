/**
 * WhatsGateway Native WhatsApp Live Chat Studio Engine
 * Full real-time two-way messaging, smart delta polling, human takeover, and optimistic dispatch.
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
    };

    // 3. DOM Elements
    const dom = {
        conversationsContainer: document.getElementById('conversationsContainer'),
        chatSearchInput: document.getElementById('chatSearchInput'),
        chatSearchClear: document.getElementById('chatSearchClear'),
        unreadFilterBadge: document.getElementById('unreadFilterBadge'),
        btnRefreshConversations: document.getElementById('btnRefreshConversations'),
        filterBtns: document.querySelectorAll('.chat-filter-btn'),

        noChatSelected: document.getElementById('noChatSelected'),
        activeChatWrapper: document.getElementById('activeChatWrapper'),
        chatActiveAvatar: document.getElementById('chatActiveAvatar'),
        chatActiveName: document.getElementById('chatActiveName'),
        chatActivePhone: document.getElementById('chatActivePhone'),
        chatActiveBotBadge: document.getElementById('chatActiveBotBadge'),
        btnToggleBot: document.getElementById('btnToggleBot'),
        botToggleIcon: document.getElementById('botToggleIcon'),
        botToggleLabel: document.getElementById('botToggleLabel'),
        btnCloseChat: document.getElementById('btnCloseChat'),
        closeChatLabel: document.getElementById('closeChatLabel'),
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

    // Helper: SVG checkmark status icons (WhatsApp Web style)
    function getStatusSvg(status) {
        if (status === 'read') {
            // Double check in Sky Blue #53bdeb
            return `<span class="inline-flex text-[#53bdeb]" title="Read / Seen">
                <svg class="w-3.5 h-3.5" viewBox="0 0 16 15" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M1 8.5l3.5 3.5 7.5-7.5m-5 7.5l1.5 1.5 6.5-6.5"/>
                </svg>
            </span>`;
        }
        if (status === 'delivered') {
            // Double check in Grey
            return `<span class="inline-flex text-slate-400" title="Delivered">
                <svg class="w-3.5 h-3.5" viewBox="0 0 16 15" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M1 8.5l3.5 3.5 7.5-7.5m-5 7.5l1.5 1.5 6.5-6.5"/>
                </svg>
            </span>`;
        }
        if (status === 'sent') {
            // Single check in Grey
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
        // Pending / Queued clock icon
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
            renderConversations();
            updateUnreadBadge();
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
            const formatted = c.formatted_phone || formatPhone(c.phone);

            return `
                <div class="conv-item p-3 transition-all cursor-pointer flex items-center gap-3 relative select-none ${isSelected ? 'bg-emerald-50/60 border-l-3 border-emerald-600' : 'hover:bg-slate-50'}" data-phone="${escapeHtml(c.phone)}" data-name="${escapeHtml(c.contact_name || '')}">
                    <div class="w-10 h-10 rounded-full ${isSelected ? 'bg-emerald-700 text-white' : 'bg-slate-800 text-white'} flex items-center justify-center font-bold text-xs font-mono shrink-0 shadow-2xs">
                        ${escapeHtml(initials)}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-0.5">
                            <h4 class="text-xs font-bold text-slate-900 truncate">${escapeHtml(c.display_name || formatted)}</h4>
                            <span class="text-[10px] text-slate-400 font-mono shrink-0 ml-1">${escapeHtml(c.time_ago)}</span>
                        </div>
                        <div class="flex items-center justify-between gap-1">
                            <p class="text-[11px] text-slate-500 truncate font-medium flex-1">${escapeHtml(c.last_message || 'No messages')}</p>
                            <div class="flex items-center gap-1 shrink-0">
                                ${isClosed ? '<span class="px-1 py-0.2 rounded text-[9px] font-bold bg-slate-100 text-slate-600 border border-slate-200">Closed</span>' : ''}
                                ${isBotPaused && !isClosed ? '<span class="px-1 py-0.2 rounded text-[9px] font-bold bg-amber-50 text-amber-700 border border-amber-200">⏸️ Takeover</span>' : ''}
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

    async function fetchMessages(phone, isDelta = false) {
        if (state.activePhone !== phone) return;

        try {
            const url = `${API_BASE}/messages.php?phone=${encodeURIComponent(phone)}${isDelta && state.lastMessageId > 0 ? `&after_id=${state.lastMessageId}` : ''}`;
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();
            if (!data.ok) throw new Error(data.error || 'Failed to load messages');

            if (data.conversation) {
                state.activeConversation = data.conversation;
                dom.chatActivePhone.textContent = data.conversation.formatted_phone || formatPhone(data.conversation.phone);
                updateBotToggleUi(data.conversation.bot_paused);
                updateChatClosedUi(data.conversation);
                const currentConv = state.conversations.find(c => c.phone === phone);
                if (currentConv && currentConv.unread_count > 0) {
                    currentConv.unread_count = 0;
                    renderConversations();
                    updateUnreadBadge();
                }
            }

            // Real-time status receipt updates for previous messages (sent -> delivered -> read)
            if (data.statuses && typeof data.statuses === 'object') {
                for (const [msgId, status] of Object.entries(data.statuses)) {
                    const bubble = document.getElementById(`chat-msg-${msgId}`);
                    if (bubble) updateMessageStatusIndicator(bubble, status);
                }
            }

            const newMsgs = data.messages || [];

            if (!isDelta) {
                // Initial load
                if (newMsgs.length === 0) {
                    dom.messagesContainer.innerHTML = `
                        <div class="h-full flex flex-col items-center justify-center text-center p-8 text-slate-400 select-none">
                            <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-300 mb-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                            </div>
                            <p class="text-xs font-semibold text-slate-500 mb-0.5">No conversation history yet</p>
                            <p class="text-[11px] text-slate-400">Type a message below to start chatting on WhatsApp.</p>
                        </div>`;
                } else {
                    dom.messagesContainer.innerHTML = '';
                    newMsgs.forEach(msg => {
                        appendMessageBubble(msg);
                        if (msg.id > state.lastMessageId) state.lastMessageId = msg.id;
                    });
                    scrollToBottom(false);
                }
            } else {
                // Delta update
                if (newMsgs.length > 0) {
                    const shouldScroll = isNearBottom();
                    newMsgs.forEach(msg => {
                        appendMessageBubble(msg);
                        if (msg.id > state.lastMessageId) state.lastMessageId = msg.id;
                    });
                    if (shouldScroll) scrollToBottom(true);
                }
            }
        } catch (err) {
            console.warn('Failed to sync messages:', err);
        }
    }

    function appendMessageBubble(msg) {
        // Prevent duplicate bubbles if optimistic insert already added it
        const existing = document.getElementById(`chat-msg-${msg.id}`);
        if (existing) {
            updateMessageStatusIndicator(existing, msg.status);
            return;
        }

        const isOutbound = Boolean(msg.is_outbound);
        const bubble = document.createElement('div');
        bubble.id = `chat-msg-${msg.id}`;
        bubble.className = `flex flex-col ${isOutbound ? 'items-end ml-auto' : 'items-start mr-auto'} max-w-[85%] md:max-w-[70%] select-text`;

        let mediaHtml = '';
        if (msg.media_url) {
            if (msg.message_type === 'image') {
                mediaHtml = `<div class="mb-1.5 rounded-lg overflow-hidden border border-black/5 max-w-sm"><a href="${escapeHtml(msg.media_url)}" target="_blank"><img src="${escapeHtml(msg.media_url)}" alt="Image" class="max-h-60 w-auto object-cover rounded-lg hover:opacity-95 transition-opacity"></a></div>`;
            } else if (msg.message_type === 'audio') {
                mediaHtml = `<div class="mb-1.5"><audio controls src="${escapeHtml(msg.media_url)}" class="h-8 max-w-[260px]"></audio></div>`;
            } else {
                mediaHtml = `
                    <a href="${escapeHtml(msg.media_url)}" target="_blank" download class="mb-1.5 p-2 rounded-lg bg-black/5 hover:bg-black/10 flex items-center gap-2 text-xs font-semibold transition-colors">
                        <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span class="truncate">Download Attachment</span>
                    </a>`;
            }
        }

        const roleLabel = msg.sender_type === 'bot' 
            ? '<span class="text-[9px] font-mono font-bold text-emerald-800 bg-emerald-100/80 px-1 rounded mr-1">BOT</span>' 
            : '';

        const bubbleBg = isOutbound
            ? 'bg-[#D9FDD3] border border-[#bdebc2]/60 rounded-2xl rounded-tr-xs shadow-2xs'
            : 'bg-white border border-slate-200/90 rounded-2xl rounded-tl-xs shadow-2xs';

        const senderHeader = '';

        bubble.innerHTML = `
            <div class="${bubbleBg} px-3 py-1.5 text-xs text-slate-800 break-words leading-relaxed max-w-full">
                ${senderHeader}
                ${mediaHtml}
                ${msg.body ? `<div>${escapeHtml(msg.body).replace(/\n/g, '<br>')}</div>` : ''}
                <div class="flex items-center justify-end gap-1 mt-0.5 text-[10px] text-slate-400 font-mono select-none">
                    ${roleLabel}
                    <span>${escapeHtml(msg.time || '')}</span>
                    ${isOutbound ? `<span class="msg-status-mark ml-0.5">${getStatusSvg(msg.status)}</span>` : ''}
                </div>
            </div>`;

        dom.messagesContainer.appendChild(bubble);
    }

    function updateMessageStatusIndicator(bubbleEl, status) {
        const mark = bubbleEl.querySelector('.msg-status-mark');
        if (!mark) return;
        mark.innerHTML = getStatusSvg(status);
    }

    function isNearBottom() {
        const threshold = 120;
        const pos = dom.messagesContainer.scrollTop + dom.messagesContainer.clientHeight;
        const max = dom.messagesContainer.scrollHeight;
        return max - pos <= threshold;
    }

    function scrollToBottom(smooth = true) {
        dom.messagesContainer.scrollTo({
            top: dom.messagesContainer.scrollHeight,
            behavior: smooth ? 'smooth' : 'instant'
        });
    }

    function updateBotToggleUi(isPaused) {
        const paused = isPaused === 1;
        if (paused) {
            dom.chatActiveBotBadge.textContent = '⏸️ Bot Paused (Takeover)';
            dom.chatActiveBotBadge.className = 'px-1.5 py-0.2 rounded text-[10px] font-bold font-mono bg-amber-50 text-amber-700 border border-amber-200';
            dom.botToggleIcon.textContent = '▶️';
            dom.botToggleLabel.textContent = 'Resume Bot';
            dom.btnToggleBot.className = 'inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100 transition-colors shadow-2xs';
        } else {
            dom.chatActiveBotBadge.textContent = '🤖 Bot Active';
            dom.chatActiveBotBadge.className = 'px-1.5 py-0.2 rounded text-[10px] font-bold font-mono bg-emerald-50 text-emerald-700 border border-emerald-200';
            dom.botToggleIcon.textContent = '⏸️';
            dom.botToggleLabel.textContent = 'Pause Bot (Takeover)';
            dom.btnToggleBot.className = 'inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-white text-slate-700 border border-slate-200 hover:bg-slate-50 transition-colors shadow-2xs';
        }
    }

    function updateChatClosedUi(conv) {
        if (!dom.btnCloseChat) return;
        const isClosed = conv && conv.status === 'closed';

        if (isClosed) {
            if (dom.chatClosedBanner) dom.chatClosedBanner.classList.remove('hidden');
            if (dom.closeChatLabel) dom.closeChatLabel.textContent = 'Reopen Chat';
            dom.btnCloseChat.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all active:scale-[0.98] border-slate-300 text-slate-700 bg-slate-100 hover:bg-slate-200';
            dom.btnCloseChat.title = 'Reopen this conversation';
        } else {
            if (dom.chatClosedBanner) dom.chatClosedBanner.classList.add('hidden');
            if (dom.closeChatLabel) dom.closeChatLabel.textContent = 'Close Chat';
            dom.btnCloseChat.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all active:scale-[0.98] border-rose-200 text-rose-700 bg-rose-50/70 hover:bg-rose-100';
            dom.btnCloseChat.title = 'Close this chat and send 5-star customer feedback request';
        }
    }

    async function handleCloseOrReopenChat(targetAction = null) {
        if (!state.activePhone) return;

        const isCurrentlyClosed = state.activeConversation && state.activeConversation.status === 'closed';
        const action = targetAction || (isCurrentlyClosed ? 'reopen' : 'close');

        if (action === 'close') {
            const confirmed = confirm('Are you sure you want to close this chat? An automated 5-star feedback request will be sent to the customer on WhatsApp.');
            if (!confirmed) return;
        }

        try {
            const res = await fetch(`${API_BASE}/close.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    phone: state.activePhone,
                    action: action,
                }),
            });
            const data = await res.json();
            if (!data.ok) throw new Error(data.error || 'Failed to update chat status');

            if (state.activeConversation) {
                state.activeConversation.status = data.status;
                state.activeConversation.awaiting_feedback = data.awaiting_feedback || 0;
            }

            const item = state.conversations.find(c => c.phone === state.activePhone);
            if (item) {
                item.status = data.status;
                item.awaiting_feedback = data.awaiting_feedback || 0;
                if (data.status === 'closed') {
                    item.last_message = 'Support chat closed. 5-star feedback requested.';
                }
            }

            updateChatClosedUi(state.activeConversation);
            renderConversations();
            await fetchMessages(state.activePhone, false);
        } catch (err) {
            alert('Could not update chat status: ' + err.message);
        }
    }

    // =========================================================================
    // SEND MESSAGE
    // =========================================================================

    async function handleSendMessage() {
        if (!state.activePhone || state.isSending) return;

        const message = dom.chatMessageInput.value.trim();
        const hasFile = Boolean(state.selectedFile);

        if (!message && !hasFile) return;

        state.isSending = true;
        dom.btnSendMessage.disabled = true;
        dom.btnSendMessage.innerHTML = `
            <svg class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
            <span>Sending...</span>`;

        // Optimistic UI bubble
        const tempId = `temp-${Date.now()}`;
        const now = new Date();
        const optimisticMsg = {
            id: tempId,
            sender_type: 'agent',
            is_outbound: true,
            message_type: hasFile ? 'document' : 'text',
            body: message,
            media_url: hasFile ? URL.createObjectURL(state.selectedFile) : null,
            status: 'queued',
            time: now.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' }),
        };
        appendMessageBubble(optimisticMsg);
        scrollToBottom(true);

        try {
            let res;
            if (hasFile) {
                const formData = new FormData();
                formData.append('phone', state.activePhone);
                formData.append('message', message);
                formData.append('file', state.selectedFile);
                formData.append('auto_pause_bot', '1');

                res = await fetch(`${API_BASE}/send.php`, {
                    method: 'POST',
                    body: formData,
                });
            } else {
                res = await fetch(`${API_BASE}/send.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        phone: state.activePhone,
                        message: message,
                        auto_pause_bot: true,
                    }),
                });
            }

            const data = await res.json();
            if (!data.ok) throw new Error(data.error || 'Failed to dispatch message');

            // Replace optimistic bubble ID with real DB ID
            const tempBubble = document.getElementById(`chat-msg-${tempId}`);
            if (tempBubble && data.message) {
                tempBubble.id = `chat-msg-${data.message.id}`;
                updateMessageStatusIndicator(tempBubble, 'sent');
                if (data.message.id > state.lastMessageId) {
                    state.lastMessageId = data.message.id;
                }
            }

            // Reset input box & attachment
            dom.chatMessageInput.value = '';
            dom.chatMessageInput.style.height = 'auto';
            clearAttachment();

            // Auto paused bot UI update
            if (data.conversation) {
                updateBotToggleUi(data.conversation.bot_paused);
                updateChatClosedUi(data.conversation);
            }

            // Refresh conversations list to update preview snippet
            fetchConversations(true);
        } catch (err) {
            alert('Failed to send WhatsApp message: ' + err.message);
            const tempBubble = document.getElementById(`chat-msg-${tempId}`);
            if (tempBubble) tempBubble.remove();
        } finally {
            state.isSending = false;
            dom.btnSendMessage.disabled = false;
            dom.btnSendMessage.innerHTML = `
                <span>Send</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>`;
            dom.chatMessageInput.focus();
        }
    }

    // =========================================================================
    // BOT TOGGLE
    // =========================================================================

    async function handleToggleBot() {
        if (!state.activePhone) return;

        try {
            const res = await fetch(`${API_BASE}/toggle-bot.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ phone: state.activePhone }),
            });
            const data = await res.json();
            if (!data.ok) throw new Error(data.error || 'Failed to toggle bot');

            updateBotToggleUi(data.bot_paused);
            fetchConversations(true);
        } catch (err) {
            alert('Could not toggle bot status: ' + err.message);
        }
    }

    // =========================================================================
    // ATTACHMENTS & UTILITIES
    // =========================================================================

    function clearAttachment() {
        state.selectedFile = null;
        dom.chatFileInput.value = '';
        dom.attachmentPreview.classList.add('hidden');
        dom.attachmentFileName.textContent = '';
    }

    function handleFileSelected(e) {
        const file = e.target.files && e.target.files[0];
        if (!file) return;

        // 20MB client check
        if (file.size > 20 * 1024 * 1024) {
            alert('File exceeds maximum upload size of 20MB.');
            dom.chatFileInput.value = '';
            return;
        }

        state.selectedFile = file;
        dom.attachmentFileName.textContent = file.name;
        dom.attachmentPreview.classList.remove('hidden');
    }

    // =========================================================================
    // REAL-TIME SMART POLLING
    // =========================================================================

    function restartMessagePolling() {
        if (state.pollTimer) clearInterval(state.pollTimer);
        state.pollTimer = setInterval(() => {
            // Only poll if document is visible and an active conversation is selected
            if (!document.hidden && state.activePhone) {
                fetchMessages(state.activePhone, true);
            }
        }, 2000);
    }

    function startConversationPolling() {
        if (state.convTimer) clearInterval(state.convTimer);
        state.convTimer = setInterval(() => {
            if (!document.hidden) {
                fetchConversations(true);
            }
        }, 4000);
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

    // Bot toggle
    dom.btnToggleBot.addEventListener('click', handleToggleBot);

    // Close / Reopen Chat
    if (dom.btnCloseChat) {
        dom.btnCloseChat.addEventListener('click', () => handleCloseOrReopenChat());
    }
    if (dom.btnReopenBanner) {
        dom.btnReopenBanner.addEventListener('click', () => handleCloseOrReopenChat('reopen'));
    }
    dom.btnCloseChat.addEventListener('click', () => handleCloseOrReopenChat());
    dom.btnReopenBanner.addEventListener('click', () => handleCloseOrReopenChat('reopen'));

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

    // Filter buttons (All / Unread)
    dom.filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            dom.filterBtns.forEach(b => {
                b.className = 'chat-filter-btn px-2.5 py-0.5 rounded-md hover:bg-slate-200/70 text-slate-600 transition-all';
            });
            btn.className = 'chat-filter-btn px-2.5 py-0.5 rounded-md bg-slate-900 text-white font-bold transition-all shadow-2xs';
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
    fetchConversations(false);
    startConversationPolling();

})();
