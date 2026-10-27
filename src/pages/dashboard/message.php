    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Compose Message</h1>
            <p class="text-[13px] text-slate-500 mt-0.5 font-medium">Queue outbound dispatches, notifications, and rich media via the Baileys WebSocket gateway.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-white border border-slate-200/80 text-slate-700 shadow-subtle">
                <span class="w-1.5 h-1.5 rounded-full <?= $deviceConnected ? 'bg-emerald-500' : 'bg-slate-400' ?>"></span>
                <span><?= $deviceConnected ? ($deviceSleeping ? 'Standby (Auto-Wake)' : 'Gateway Active') : 'Gateway Offline' ?></span>
            </span>
        </div>
    </div>

    <!-- Integrated Telemetry Ribbon -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-subtle mb-6 overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-slate-100">
            <div class="p-4 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-semibold text-slate-500 tracking-tight">Target Audience Pool</div>
                    <div class="text-xl font-bold text-slate-900 font-mono tabular-nums tracking-tight mt-0.5"><?= count($contactOptions) ?></div>
                    <div class="text-[11px] text-slate-400 mt-0.5"><?= $optedInCount ?> opted-in &bull; <?= count($campaigns) ?> campaigns</div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-500 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
            </div>
            <div class="p-4 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-semibold text-slate-500 tracking-tight">Anti-Ban Dispatch Engine</div>
                    <div class="text-xl font-bold text-emerald-600 tracking-tight mt-0.5">Humanized</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Randomized inter-packet delay active</div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </div>
            </div>
            <div class="p-4 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-semibold text-slate-500 tracking-tight">Payload Capabilities</div>
                    <div class="text-xl font-bold text-slate-900 tracking-tight mt-0.5">Multi-Format Media</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Images, Audio, PDF, MP4 &le; 15MB</div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-500 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Message Composer Card -->
    <section class="bg-white rounded-xl border border-slate-200/80 shadow-card">
        <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/40 rounded-t-xl">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-600 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                </div>
                <div>
                    <h2 class="text-[13px] font-bold text-slate-900 tracking-tight">New Dispatch Composer</h2>
                    <p class="text-[11px] text-slate-500">Configure target channel, attach media, and format your outbound message.</p>
                </div>
            </div>
            <span class="px-2.5 py-1 rounded-md text-[11px] font-semibold bg-white text-slate-600 border border-slate-200/80 shadow-subtle flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Pipeline Ready
            </span>
        </div>
        
        <div class="p-5 sm:p-6">
            <form method="post" action="<?= e(base_url('index.php?page=message-send')) ?>" enctype="multipart/form-data" class="space-y-5">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                    <!-- Target Selection -->
                    <div class="flex flex-col justify-between">
                        <div>
                            <label class="block text-[12px] font-semibold text-slate-700 mb-1.5">Target Recipient</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <div>
                                    <div class="relative">
                                        <select name="recipient_mode" id="recipientMode" data-message-mode-select class="block w-full pl-3 pr-8 py-2 text-[12px] border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-white appearance-none transition-all shadow-subtle font-medium text-slate-700 cursor-pointer">
                                            <option value="contact">Saved Contact</option>
                                            <option value="campaign">Campaign Audience</option>
                                            <option value="number">Direct Number</option>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 flex items-center px-2.5 pointer-events-none text-slate-400">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                        </div>
                                    </div>
                                </div>

                                <!-- Dynamic Target Inputs -->
                                <div>
                                    <!-- Contact -->
                                    <div data-message-panel="contact">
                                        <div class="relative">
                                            <select name="contact_id" class="block w-full pl-3 pr-8 py-2 text-[12px] border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-white appearance-none transition-all shadow-subtle text-slate-700 font-medium cursor-pointer">
                                                <option value="">-- Choose recipient --</option>
                                                <?php foreach ($contactOptions as $contact): ?>
                                                    <option value="<?= (int) $contact['id'] ?>"><?= e($contact['name']) ?> &middot; <?= e($contact['phone']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center px-2.5 pointer-events-none text-slate-400">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path></svg>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Campaign -->
                                    <div data-message-panel="campaign" hidden>
                                        <div class="relative">
                                            <select name="campaign_id" class="block w-full pl-3 pr-8 py-2 text-[12px] border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-white appearance-none transition-all shadow-subtle text-slate-700 font-medium cursor-pointer">
                                                <option value="">-- Choose campaign --</option>
                                                <?php foreach ($campaigns as $campaign): ?>
                                                    <option value="<?= (int) $campaign['id'] ?>">#<?= (int) $campaign['id'] ?> - <?= e($campaign['name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center px-2.5 pointer-events-none text-slate-400">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path></svg>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- New Number -->
                                    <div data-message-panel="number" hidden>
                                        <input name="manual_number" type="text" placeholder="+1234567890 (E.164)" class="block w-full px-3 py-2 text-[12px] border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-white transition-all shadow-subtle font-mono tabular-nums text-slate-800">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1.5">Route instantly to contacts, dynamic campaign lists, or manual phone numbers.</p>
                    </div>

                    <!-- Attachment Upload -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-[12px] font-semibold text-slate-700">Media Attachment <span class="text-slate-400 font-normal">(Optional)</span></label>
                            <span class="text-[11px] text-slate-400 font-mono">Max 15MB</span>
                        </div>
                        <div class="relative group border border-dashed border-slate-300 hover:border-emerald-500 rounded-lg px-3 py-2 bg-slate-50/50 hover:bg-emerald-50/20 transition-all cursor-pointer flex items-center gap-3">
                            <input id="messageAttachment" name="attachment" type="file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" accept=".jpg,.jpeg,.png,.webp,.mp4,.pdf,.doc,.docx,.txt,image/*,video/mp4,application/pdf,text/plain,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
                            <div class="w-7 h-7 rounded-md bg-white border border-slate-200 group-hover:border-emerald-300 group-hover:text-emerald-600 shadow-subtle flex items-center justify-center text-slate-400 shrink-0 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                            </div>
                            <div class="min-w-0 flex-1 flex items-center justify-between gap-2">
                                <span class="text-[12px] font-medium text-slate-700 group-hover:text-emerald-700 transition-colors truncate" id="attachmentStatus">No file selected</span>
                                <span class="shrink-0 text-[10px] uppercase font-bold tracking-wider px-1.5 py-0.5 rounded bg-white border border-slate-200 text-slate-500 group-hover:border-emerald-200 group-hover:text-emerald-600 shadow-subtle">Browse</span>
                            </div>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1.5">Supported: Images, MP4, PDF, DOCX, TXT.</p>
                    </div>
                </div>

                <!-- Message Body -->
                <div class="pt-2 border-t border-slate-100">
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-[12px] font-semibold text-slate-700">Message Content</label>
                        <span class="text-[11px] text-slate-400 font-mono">WYSIWYG &amp; Live WA Preview</span>
                    </div>
                    <textarea name="message" data-presets='["{name}", "{phone}"]' rows="5" class="wa-editor block w-full px-3.5 py-3 text-[15px] sm:text-[16px] border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-white transition-all shadow-subtle placeholder:text-slate-300 font-sans leading-relaxed" placeholder="Type your WhatsApp message here..." required></textarea>
                </div>

                <!-- Footer Actions -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t border-slate-100">
                    <label class="flex items-center gap-2.5 cursor-pointer group">
                        <input type="checkbox" name="humanize" value="1" checked class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 focus:ring-offset-0 transition-colors cursor-pointer">
                        <span class="text-[12px] font-semibold text-slate-700 group-hover:text-slate-900 transition-colors flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Humanized Delivery Mode (Prevents spam detection)
                        </span>
                    </label>
                    <div class="flex items-center gap-2">
                        <button type="reset" class="px-3.5 py-2 text-[12px] font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100/70 rounded-lg transition-colors btn-tactile active:scale-[0.98]">Clear form</button>
                        <button type="submit" class="px-4 py-2 text-[12px] font-semibold text-white bg-slate-900 rounded-lg shadow-subtle hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 transition-all flex items-center gap-2 btn-tactile active:scale-[0.98]">
                            <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                            <span>Queue Message</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </section>
    
    <!-- Footer Spacer -->
    <div class="h-8"></div>

