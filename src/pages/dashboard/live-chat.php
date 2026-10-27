<?php
declare(strict_types=1);

/**
 * Native WhatsApp Live Chat Studio
 * Integrated WhatsApp Web style console with no outer page scroll, direct two-way messaging,
 * human takeover, and real-time status receipts.
 */
?>

<!-- Main Live Chat Studio Viewport Card (Fixed Full Height, No Outer Scroll) -->
<div class="bg-white rounded-xl border border-slate-200/90 shadow-sm overflow-hidden h-full min-h-0 flex flex-1">
    
    <!-- ========================================== -->
    <!-- LEFT PANEL: CONVERSATION LIST & SEARCH     -->
    <!-- ========================================== -->
    <div class="w-80 md:w-96 flex-shrink-0 border-r border-slate-200/80 flex flex-col bg-slate-50/40 min-h-0">
        
        <!-- Sidebar Top Header (WhatsApp Web style: Connection Pill + New Chat) -->
        <div class="p-3 border-b border-slate-200/80 bg-white flex items-center justify-between gap-2 shrink-0">
            <!-- Connection Status Pill -->
            <div class="flex items-center gap-1.5 min-w-0">
                <?php if ($isDeviceConnected): ?>
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200/80 text-emerald-800 text-[11px] font-semibold truncate" title="WhatsApp Device Connected">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>
                        <span class="truncate font-mono font-bold"><?= e(!empty($chatDeviceSession['push_name']) ? $chatDeviceSession['push_name'] : '+' . ($chatDeviceSession['phone'] ?? '91')) ?></span>
                    </div>
                <?php else: ?>
                    <a href="<?= e(base_url('index.php?page=device')) ?>" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-50 border border-amber-200/80 text-amber-800 text-[11px] font-semibold hover:bg-amber-100 transition-colors" title="Device Offline — Click to Connect">
                        <span class="w-2 h-2 rounded-full bg-amber-500 shrink-0"></span>
                        <span>Offline • Connect</span>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Action Buttons: + New Chat & Refresh -->
            <div class="flex items-center gap-1 shrink-0">
                <button id="btnNewChat" type="button" class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-semibold shadow-xs transition-all active:scale-[0.98]" title="Start a new chat with an Indian or international number">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>New Chat</span>
                </button>
                <button id="btnRefreshConversations" type="button" title="Refresh inbox" class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                </button>
            </div>
        </div>

        <!-- Search Bar & Filter Chips -->
        <div class="p-2.5 border-b border-slate-200/70 bg-white/70 shrink-0 space-y-2">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" id="chatSearchInput" placeholder="Search phone (+91...) or name" class="w-full pl-8 pr-7 py-1.5 bg-slate-100 border border-slate-200/90 rounded-lg text-xs focus:bg-white focus:outline-none focus:ring-1 focus:ring-slate-900 focus:border-slate-900 transition-all font-medium placeholder-slate-400">
                <button id="chatSearchClear" type="button" class="absolute inset-y-0 right-0 pr-2 flex items-center text-slate-400 hover:text-slate-600 hidden">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Filter Tabs -->
            <div class="flex items-center gap-1.5 text-[11px] font-semibold text-slate-500">
                <button type="button" data-filter="all" class="chat-filter-btn px-2.5 py-0.5 rounded-md bg-slate-900 text-white font-bold transition-all shadow-2xs">All</button>
                <button type="button" data-filter="unread" class="chat-filter-btn px-2.5 py-0.5 rounded-md hover:bg-slate-200/70 text-slate-600 transition-all">Unread <span id="unreadFilterBadge" class="ml-0.5 text-[9px] font-mono <?= $totalChatUnread > 0 ? 'text-emerald-700 font-bold' : '' ?>"><?= $totalChatUnread > 0 ? "({$totalChatUnread})" : '' ?></span></button>
                <button type="button" data-filter="closed" class="chat-filter-btn px-2.5 py-0.5 rounded-md hover:bg-slate-200/70 text-slate-600 transition-all">Closed</button>
            </div>
        </div>

        <!-- Conversation Cards Stream (Independent Scroll) -->
        <div id="conversationsContainer" class="flex-1 overflow-y-auto divide-y divide-slate-100 custom-scrollbar min-h-0 bg-white">
            <div class="p-8 text-center text-slate-400">
                <svg class="w-6 h-6 mx-auto mb-2 text-slate-300 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                <p class="text-xs">Loading conversations...</p>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- RIGHT PANEL: ACTIVE CONVERSATION THREAD    -->
    <!-- ========================================== -->
    <div id="chatPane" class="flex-1 flex flex-col bg-[#F8FAFC] relative min-h-0 overflow-hidden">
        
        <!-- Empty State (No Chat Selected) -->
        <div id="noChatSelected" class="flex-1 flex flex-col items-center justify-center p-8 text-center select-none">
            <div class="w-16 h-16 rounded-2xl bg-white border border-slate-200/80 shadow-sm flex items-center justify-center text-emerald-600 mb-3">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
            </div>
            <h2 class="text-sm font-bold text-slate-900 tracking-tight mb-1">WhatsApp Live Chat</h2>
            <p class="text-xs text-slate-500 max-w-sm">Select a contact from the left conversation list or click <strong>"New Chat"</strong> to start messaging directly.</p>
        </div>

        <!-- Active Chat Container (Hidden until chat selected) -->
        <div id="activeChatWrapper" class="flex-1 flex flex-col h-full min-h-0 hidden">
            
            <!-- Active Chat Top Header -->
            <div class="h-14 px-4 border-b border-slate-200/80 bg-white flex items-center justify-between shrink-0 shadow-2xs z-10">
                <div class="flex items-center gap-3 min-w-0">
                    <div id="chatActiveAvatar" class="w-9 h-9 rounded-full bg-slate-900 text-white font-bold text-xs flex items-center justify-center font-mono shrink-0 shadow-2xs">WA</div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <h2 id="chatActiveName" class="text-xs font-bold text-slate-900 truncate">Recipient</h2>
                            <span id="chatActiveBotBadge" class="px-1.5 py-0.2 rounded text-[10px] font-bold font-mono"></span>
                        </div>
                        <p id="chatActivePhone" class="text-[11px] text-slate-400 font-mono font-medium truncate">+91 00000 00000</p>
                    </div>
                </div>

                <!-- Action Buttons: Toggle Bot & Close Chat -->
                <div class="flex items-center gap-2 shrink-0">
                    <button id="btnToggleBot" type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all active:scale-[0.98]" title="Pause bot to take over manually, or resume automated replies">
                        <span id="botToggleIcon">🤖</span>
                        <span id="botToggleLabel">Bot Active</span>
                    </button>
                    <button id="btnCloseChat" type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all active:scale-[0.98] border-rose-200 text-rose-700 bg-rose-50/70 hover:bg-rose-100" title="Close this chat and send 5-star customer feedback request">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span id="closeChatLabel">Close Chat</span>
                    </button>
                </div>
            </div>

            <!-- Closed Chat Notice Banner (Shown dynamically when conversation is marked closed) -->
            <div id="chatClosedBanner" class="hidden px-4 py-2 bg-amber-50/90 border-b border-amber-200/80 text-amber-900 text-xs flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500 shrink-0"></span>
                    <span><strong>Chat Closed:</strong> Feedback rating requested. Any customer reply will reopen or log to <a href="<?= e(base_url('index.php?page=feedback')) ?>" class="underline font-bold hover:text-amber-950">Feedback</a>.</span>
                </div>
                <button id="btnReopenBanner" type="button" class="text-[11px] font-bold text-amber-800 hover:text-amber-950 underline ml-2 shrink-0">Reopen</button>
            </div>

            <!-- Messages Stream (Independent Scroll, WhatsApp Style Wallpaper) -->
            <div id="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-2.5 custom-scrollbar min-h-0 bg-[#EFEAE2]/30" style="background-image: radial-gradient(#cbd5e1 0.75px, transparent 0.75px); background-size: 16px 16px;">
                <!-- Messages populated dynamically -->
            </div>

            <!-- Message Input & Attachment Bar (Fixed at bottom) -->
            <div class="p-2.5 border-t border-slate-200/80 bg-white shrink-0">
                <form id="chatSendForm" class="flex items-end gap-2" onsubmit="return false;">
                    
                    <!-- File input trigger -->
                    <input type="file" id="chatFileInput" class="hidden" accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx,.txt">
                    <button type="button" id="btnAttachFile" class="p-2 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors shrink-0" title="Attach file or photo">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                    </button>

                    <!-- Message Textarea -->
                    <div class="flex-1 relative min-w-0">
                        <textarea id="chatMessageInput" rows="1" placeholder="Type a message... (Enter to send, Shift+Enter for newline)" class="w-full resize-none py-2 px-3 bg-slate-50 border border-slate-200/90 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-1 focus:ring-slate-900 focus:border-slate-900 transition-all font-medium max-h-32 custom-scrollbar"></textarea>
                        
                        <!-- Selected attachment pill -->
                        <div id="attachmentPreview" class="hidden mt-1.5 inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 border border-slate-200 rounded-md text-[11px] font-mono text-slate-700">
                            <span id="attachmentFileName" class="truncate max-w-[200px]">file.pdf</span>
                            <button type="button" id="btnRemoveAttachment" class="text-slate-400 hover:text-rose-600">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Send Button -->
                    <button type="submit" id="btnSendMessage" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold inline-flex items-center gap-1.5 transition-all shadow-xs active:scale-[0.98] shrink-0">
                        <span>Send</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Start New Chat Dialog (Default +91 India Country Code) -->
<div id="newChatModal" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-sm w-full p-5 shadow-2xl border border-slate-200 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-bold text-slate-900">Start New Conversation</h3>
            <button type="button" id="btnCloseNewChatModal" class="text-slate-400 hover:text-slate-600 p-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <p class="text-xs text-slate-500 mb-4">Enter a 10-digit Indian mobile number or international phone number with country code.</p>
        
        <form id="newChatForm" onsubmit="return false;" class="space-y-3">
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Phone Number (default country code: 91)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center text-xs font-mono font-bold text-slate-400 pointer-events-none">+</span>
                    <input type="text" id="newChatPhone" value="91" placeholder="919876543210 or 9876543210" required class="w-full pl-6 pr-3 py-1.5 border border-slate-200 rounded-lg text-xs font-mono focus:ring-1 focus:ring-slate-900 focus:outline-none">
                </div>
                <p class="text-[10px] text-slate-400 mt-1">If 10 digits are entered, 91 is automatically prefixed.</p>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Contact Name (optional)</label>
                <input type="text" id="newChatName" placeholder="e.g. Navneet Yadav" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs focus:ring-1 focus:ring-slate-900 focus:outline-none">
            </div>
            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" id="btnCancelNewChat" class="px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg">Cancel</button>
                <button type="submit" class="px-3.5 py-1.5 text-xs font-bold bg-slate-900 hover:bg-slate-800 text-white rounded-lg shadow-xs">Open Chat</button>
            </div>
        </form>
    </div>
</div>

<!-- Load Live Chat Client Script -->
<script src="<?= e(base_url('assets/live-chat.js')) ?>?v=<?= time() ?>"></script>
