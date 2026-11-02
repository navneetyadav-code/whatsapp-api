    <?php if (!$isOwner && !$allowNewDevices): ?>
    <div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200/80 text-amber-900 flex items-start gap-3 shadow-subtle">
        <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        <div>
            <h4 class="text-sm font-bold text-amber-950">Device Linking Restricted</h4>
            <p class="text-xs text-amber-800 mt-0.5"><?= e($restrictedNotice) ?></p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Page Header & Global Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">WhatsApp Device</h1>
            <p class="text-[13px] text-slate-500 mt-0.5 font-medium">Link, monitor, and configure your WhatsApp Web Baileys multi-device socket session.</p>
        </div>
        <div class="flex gap-2">
            <button type="button" id="disconnectBtn" data-session-action="logout" class="px-3.5 py-1.5 text-[12px] font-semibold text-rose-700 bg-white border border-rose-200 rounded-lg shadow-subtle hover:bg-rose-50 focus:outline-none focus:ring-2 focus:ring-rose-200 transition-all text-center btn-tactile active:scale-[0.98] <?= $deviceConnected ? '' : 'hidden' ?>">
                Logout Device
            </button>
        </div>
    </div>

    <!-- Timelock Alert Banner (Anti-Ban Protection) -->
    <?php 
    $timelockActive = !empty($session['reachout_timelock_active']);
    $timelockEndsAt = !empty($session['reachout_timelock_ends_at']) ? strtotime($session['reachout_timelock_ends_at']) : 0;
    if ($timelockActive && $timelockEndsAt > 0 && $timelockEndsAt < time()) {
        $timelockActive = false;
    }
    ?>
    <?php if ($timelockActive): ?>
    <div class="mb-6 p-4 rounded-xl border border-rose-200 bg-rose-50/80 shadow-subtle flex items-start gap-3.5">
        <div class="w-9 h-9 rounded-lg bg-rose-100 border border-rose-200 flex items-center justify-center text-rose-700 shrink-0 mt-0.5">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        </div>
        <div class="flex-1">
            <div class="flex items-center gap-2">
                <h3 class="text-[14px] font-bold text-rose-900 tracking-tight">WhatsApp Reachout Restriction Active (Anti-Ban Protection Engaged)</h3>
                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-rose-200 text-rose-800 uppercase tracking-wide">Timelock</span>
            </div>
            <p class="text-[12px] text-rose-700 mt-1 leading-relaxed">
                WhatsApp has placed a temporary <strong>Reachout Timelock</strong> on this phone number. You are restricted from messaging new contacts until 
                <strong><?= $timelockEndsAt ? date('M d, Y h:i A', $timelockEndsAt) : 'further notice' ?></strong>.
            </p>
            <div class="mt-2 text-[11px] font-medium text-rose-600 bg-white/60 px-3 py-1.5 rounded-lg border border-rose-200/60 inline-flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                Cold outreach & campaign broadcasts are automatically paused to prevent a permanent ban. Existing customer replies remain active.
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Integrated Telemetry Ribbon -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-subtle mb-6 overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-slate-100">
            <div class="p-4 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-semibold text-slate-500 tracking-tight">Gateway Socket State</div>
                    <div class="flex items-center gap-2 mt-1">
                        <span id="statusIndicator" class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 <?= $deviceConnected ? ($deviceSleeping ? 'bg-amber-400' : 'bg-emerald-400') : 'bg-slate-400' ?>"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 <?= $deviceConnected ? ($deviceSleeping ? 'bg-amber-500' : 'bg-emerald-500') : 'bg-slate-500' ?>"></span>
                        </span>
                        <h3 class="text-xl font-bold text-slate-900 tracking-tight" id="statusText"><?= $deviceConnected ? ($deviceSleeping ? 'Sleeping' : 'Online') : 'Offline' ?></h3>
                    </div>
                    <div class="text-[11px] text-slate-400 mt-0.5">WebSocket session heartbeat</div>
                </div>
                <div class="w-8 h-8 rounded-lg <?= $deviceConnected ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-slate-50 text-slate-500 border border-slate-100' ?> flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                </div>
            </div>
            <div class="p-4 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-semibold text-slate-500 tracking-tight">Active Linked Identity</div>
                    <div class="text-xl font-bold text-slate-900 font-mono tabular-nums tracking-tight mt-0.5" id="topPhoneText"><?= e($session['phone'] ?? 'None') ?></div>
                    <div class="text-[11px] text-slate-400 mt-0.5"><?= e($session['push_name'] ?? 'Not registered') ?></div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-500 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                </div>
            </div>
            <div class="p-4 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-semibold text-slate-500 tracking-tight">Baileys Engine Protocol</div>
                    <div class="text-xl font-bold text-slate-900 tracking-tight mt-0.5">Multi-Device v2</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">End-to-end encrypted protocol</div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-500 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- 2-Column Pairing & Session Deck -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch mb-6">
        <!-- Device Info Card -->
        <section class="connection-details-card bg-white rounded-xl border border-slate-200/80 shadow-card overflow-hidden flex flex-col h-full">
            <div class="px-5 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <div>
                    <h2 class="text-[14px] font-bold text-slate-900 tracking-tight">Connection Details</h2>
                    <p class="text-[12px] text-slate-500 mt-0.5">Live session attributes & protocol state.</p>
                </div>
                <span id="statusBadge" class="px-2.5 py-1 text-[11px] font-semibold rounded-full border shadow-subtle flex items-center gap-1.5 <?= $deviceConnected ? ($deviceSleeping ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200') : 'bg-slate-100 text-slate-700 border-slate-200' ?>"><?= $deviceConnected ? ($deviceSleeping ? 'Sleeping' : 'Connected') : 'Disconnected' ?></span>
            </div>
            
            <div class="p-5 sm:p-6 space-y-4 flex-1 flex flex-col justify-center divide-y divide-slate-100">
                <div class="flex items-start gap-3.5 pb-3">
                    <div class="w-9 h-9 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-500 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold text-slate-500 tracking-tight">Linked Number</p>
                        <p class="text-[14px] font-mono font-semibold text-slate-900 mt-0.5 tabular-nums" id="phoneText"><?= e($session['phone'] ?? 'Not connected') ?></p>
                    </div>
                </div>
                
                <div class="flex items-start gap-3.5 pt-3 pb-3">
                    <div class="w-9 h-9 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-500 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold text-slate-500 tracking-tight">WhatsApp Profile Name</p>
                        <p class="text-[14px] font-semibold text-slate-900 mt-0.5" id="nameText"><?= e($session['push_name'] ?? 'Unknown') ?></p>
                    </div>
                </div>
                
                <div class="flex items-start gap-3.5 pt-3">
                    <div class="w-9 h-9 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-500 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold text-slate-500 tracking-tight">Last Connected</p>
                        <p class="text-[14px] font-mono text-slate-900 mt-0.5 tabular-nums" id="lastConnectedText"><?= e($session['connected_at'] ?? 'Never') ?></p>
                    </div>
                </div>
                
                <div id="lastErrorPanel" class="<?= empty($session['last_error']) ? 'hidden' : '' ?> pt-3">
                    <div class="p-3 bg-rose-50/70 border border-rose-200/80 rounded-lg flex items-start gap-3">
                        <svg class="w-4 h-4 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <div>
                            <p class="text-[11px] font-semibold text-rose-700">Last Error</p>
                            <p id="lastErrorText" class="text-[12px] font-mono text-rose-800 mt-0.5"><?= e($session['last_error'] ?? '') ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-100 flex flex-wrap items-center gap-2 mt-auto" id="deviceActions">
                <?php if ($isOwner || $allowNewDevices): ?>
                <button id="connectBtn" type="button" data-session-action="start" class="<?= $deviceConnected ? 'hidden' : '' ?> px-3.5 py-2 text-[12px] font-semibold text-white bg-slate-900 rounded-lg shadow-subtle hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 transition-all flex items-center gap-2 btn-tactile active:scale-[0.98]">
                    <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                    <span>Connect via WhatsApp</span>
                </button>
                <?php else: ?>
                <div class="text-[11px] text-amber-800 bg-amber-50 border border-amber-200 px-3 py-1.5 rounded-lg font-medium">
                    New device linking is disabled.
                </div>
                <?php endif; ?>

                <button id="sleepBtn" type="button" data-session-action="sleep" class="<?= !$deviceConnected || $deviceSleeping ? 'hidden' : '' ?> px-3 py-1.5 text-[12px] font-semibold text-amber-700 bg-amber-50 border border-amber-200 rounded-lg shadow-subtle hover:bg-amber-100 transition-all flex items-center gap-1.5 btn-tactile active:scale-[0.98]">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                    <span>Sleep Connection</span>
                </button>
                
                <button id="resetBtn" type="button" data-session-action="logout" data-confirm="Are you sure you want to reset the WhatsApp session? This will force a logout." class="px-3 py-1.5 text-[12px] font-semibold text-rose-700 bg-rose-50 border border-rose-200 rounded-lg shadow-subtle hover:bg-rose-100 transition-all flex items-center gap-1.5 btn-tactile active:scale-[0.98]">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    <span>Reset Session</span>
                </button>
                
                <button id="wakeupBtn" type="button" data-session-action="wake" class="<?= !$deviceConnected || !$deviceSleeping ? 'hidden' : '' ?> px-3 py-1.5 text-[12px] font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg shadow-subtle hover:bg-emerald-100 transition-all flex items-center gap-1.5 btn-tactile active:scale-[0.98]">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    <span>Wake Up Connection</span>
                </button>
            </div>
        </section>

        <!-- QR Code / Profile Card -->
        <section class="device-pair-card bg-white rounded-xl border border-slate-200/80 shadow-card overflow-hidden flex flex-col items-center justify-center p-6 text-center h-full">
            <div id="profileContainer" class="<?= $deviceConnected ? '' : 'hidden' ?> flex flex-col items-center">
                <div class="relative mb-4">
                    <img id="profileImage" src="" alt="WhatsApp Profile" class="w-28 h-28 rounded-full border-2 border-emerald-500/20 shadow-card object-cover bg-slate-100">
                    <div class="absolute bottom-1 right-1 w-5 h-5 bg-emerald-500 border-2 border-white rounded-full"></div>
                </div>
                <h3 class="text-lg font-bold text-slate-900 tracking-tight" id="profileName"><?= e($session['push_name'] ?? 'Connected') ?></h3>
                <p class="text-[12px] text-slate-500 mt-0.5">Your WhatsApp device is linked and active.</p>
            </div>

            <div id="qrContainer" class="<?= $deviceConnected ? 'hidden' : '' ?> flex flex-col items-center w-full">
                <?php if (!$isOwner && !$allowNewDevices): ?>
                <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center mb-3 border border-amber-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <h2 class="text-[15px] font-bold text-slate-900">Device Linking Restricted</h2>
                <p class="text-[12px] text-amber-800 mt-1 max-w-[280px] leading-relaxed font-medium"><?= e($restrictedNotice) ?></p>
                <?php else: ?>
                <!-- Pairing Method Toggle Tabs -->
                <div class="flex items-center bg-slate-100 p-1 rounded-xl mb-4 w-full max-w-[280px] border border-slate-200/80">
                    <button type="button" id="tabQrBtn" class="flex-1 py-1.5 px-3 text-[12px] font-semibold rounded-lg transition-all shadow-sm bg-white text-slate-900 flex items-center justify-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                        <span>QR Code</span>
                    </button>
                    <button type="button" id="tabPairCodeBtn" class="flex-1 py-1.5 px-3 text-[12px] font-semibold rounded-lg transition-all text-slate-600 hover:text-slate-900 flex items-center justify-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        <span>Phone Code</span>
                    </button>
                </div>

                <!-- Tab Pane 1: QR Code -->
                <div id="paneQr" class="w-full flex flex-col items-center">
                    <h2 class="text-[15px] font-bold text-slate-900">Pair via QR Code</h2>
                    <p class="text-[12px] text-slate-500 mt-0.5 mb-3.5 max-w-[260px]">Open WhatsApp &gt; <strong>Linked devices</strong> &gt; scan this QR code to connect.</p>
                    
                    <style>#qrPlaceholder.hidden, #qrPlaceholder[hidden] { display: none !important; }</style>
                    <div class="relative w-[210px] h-[210px] bg-white border border-slate-200/90 rounded-xl shadow-subtle p-3 flex items-center justify-center mx-auto" id="qrBox">
                        <div id="qrPlaceholder" class="w-full h-full bg-slate-50/80 rounded-lg border-2 border-dashed border-slate-200 p-3 text-center flex flex-col items-center justify-center">
                            <svg class="w-8 h-8 mb-2 text-slate-400 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                            <span class="text-[11px] font-medium text-slate-500 max-w-[160px] leading-relaxed">Click Connect to generate QR code</span>
                        </div>
                        <img id="qrImage" alt="WhatsApp QR code" class="w-full h-full rounded-lg object-contain block mx-auto hidden shadow-subtle" style="aspect-ratio: 1/1;">
                    </div>
                </div>

                <!-- Tab Pane 2: Phone Pairing Code -->
                <div id="panePairCode" class="w-full flex flex-col items-center hidden">
                    <h2 class="text-[15px] font-bold text-slate-900">Pair via Phone Number</h2>
                    <p class="text-[12px] text-slate-500 mt-0.5 mb-3.5 max-w-[280px]">Enter your WhatsApp number to receive an 8-character pairing code without scanning.</p>

                    <form id="pairCodeForm" class="w-full max-w-[300px] space-y-3">
                        <div class="space-y-1 text-left">
                            <label for="pairPhoneInput" class="block text-[11px] font-semibold text-slate-700">Phone Number (with Country Code)</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 font-mono text-xs">+</span>
                                <input type="tel" id="pairPhoneInput" placeholder="15551234567" required class="w-full pl-7 pr-3 py-2 text-[13px] border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-white shadow-subtle font-mono tabular-nums text-slate-900" autocomplete="tel">
                            </div>
                            <p class="text-[10px] text-slate-400">Include country code without '+' or spaces (e.g. 1 for US, 44 for UK, 91 for IN).</p>
                        </div>
                        
                        <button type="submit" id="requestPairCodeBtn" class="w-full py-2 px-4 text-[12px] font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-subtle transition-all flex items-center justify-center gap-2 btn-tactile active:scale-[0.98]">
                            <svg id="pairCodeBtnSpinner" class="animate-spin h-3.5 w-3.5 text-white hidden" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span id="pairCodeBtnText">Get 8-Digit Code</span>
                        </button>
                    </form>

                    <!-- Pairing Code Result Display Box -->
                    <div id="pairCodeDisplay" class="hidden mt-4 p-4 bg-slate-50 border border-indigo-100 rounded-xl w-full max-w-[320px]">
                        <div class="text-[11px] font-semibold text-indigo-700 mb-1">Your WhatsApp Pairing Code:</div>
                        <div class="flex items-center justify-center gap-2 my-2">
                            <div id="pairCodeValue" class="text-xl sm:text-2xl font-mono font-bold tracking-widest text-slate-900 select-all px-3.5 py-1.5 bg-white rounded-lg border border-slate-200 shadow-sm">
                                --------
                            </div>
                            <button type="button" id="copyPairCodeBtn" title="Copy code" class="p-2 text-slate-600 hover:text-indigo-600 hover:bg-white rounded-lg border border-slate-200 transition-all bg-white shadow-subtle active:scale-95">
                                <svg id="copyIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                <svg id="copiedIcon" class="w-4 h-4 text-emerald-600 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </button>
                        </div>
                        <div class="text-left text-[11px] text-slate-600 mt-2.5 pt-2.5 border-t border-slate-200/80 space-y-1">
                            <div class="font-semibold text-slate-700">How to enter on phone:</div>
                            <ol class="list-decimal list-inside space-y-0.5 text-[11px] text-slate-500">
                                <li>Open <strong>WhatsApp</strong> on your phone</li>
                                <li>Tap <strong>Settings / More options</strong> &gt; <strong>Linked Devices</strong></li>
                                <li>Tap <strong>Link a Device</strong></li>
                                <li>Tap <strong>Link with phone number instead</strong></li>
                                <li>Enter the 8-character code above</li>
                            </ol>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <!-- Auto-Sleep Configuration Card -->
    <section class="bg-white rounded-xl border border-slate-200/80 shadow-card overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <div>
                <h2 class="text-[14px] font-bold text-slate-900 tracking-tight">Session Auto-Sleep Settings</h2>
                <p class="text-[12px] text-slate-500 mt-0.5">Automatically suspend the socket on idle to conserve memory and network resources.</p>
            </div>
            <span class="px-2.5 py-1 text-[11px] font-semibold rounded-full border shadow-subtle <?= !empty($session['auto_sleep_enabled']) ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-600 border-slate-200' ?>">
                <?= !empty($session['auto_sleep_enabled']) ? 'Enabled' : 'Disabled' ?>
            </span>
        </div>
        <form method="post" action="<?= e(base_url('index.php?page=session-auto-sleep')) ?>" class="p-5 sm:p-6 space-y-4">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            
            <div class="flex items-start gap-3">
                <input type="checkbox" id="autoSleepEnabled" name="auto_sleep_enabled" value="1" <?= !empty($session['auto_sleep_enabled']) ? 'checked' : '' ?> class="mt-0.5 h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                <div>
                    <label for="autoSleepEnabled" class="text-[13px] font-semibold text-slate-900 cursor-pointer">Enable Session Auto-Sleep</label>
                    <p class="text-[11px] text-slate-500 mt-0.5">Automatically puts this WhatsApp session to sleep when inactive.</p>
                </div>
            </div>

            <div class="max-w-xs">
                <label for="autoSleepTimeout" class="block text-[12px] font-semibold text-slate-700 mb-1">Idle Timeout (seconds)</label>
                <input type="number" id="autoSleepTimeout" name="auto_sleep_timeout_seconds" min="15" max="86400" value="<?= (int) ($session['auto_sleep_timeout_seconds'] ?? 60) ?>" required class="w-full px-3 py-2 text-[13px] border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-white shadow-subtle font-mono tabular-nums">
                <p class="text-[11px] text-slate-400 mt-1">Default: 60s. Allowed range: 15 to 86,400 seconds (24h).</p>
            </div>

            <div class="p-3.5 bg-amber-50/70 border border-amber-200/80 rounded-lg text-amber-900 text-[11px] leading-relaxed">
                <p class="font-semibold flex items-center gap-1.5 mb-1 text-amber-800">
                    <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    Operational Disclaimers
                </p>
                <ul class="list-disc list-inside space-y-0.5 text-amber-800/90 text-[11px]">
                    <li><strong>Inbound & Chatbot:</strong> When asleep, inbound message events pause until session auto-wake or outbound activity.</li>
                    <li><strong>Compliance:</strong> Auto-sleep is a resource optimization and does not constitute an anti-ban guarantee.</li>
                    <li><strong>Operational Independence:</strong> Auto-sleep is an idle power-saving mechanism strictly separate from business working hours and campaign scheduling.</li>
                </ul>
            </div>

            <button type="submit" class="px-4 py-2 text-[12px] font-semibold text-white bg-slate-900 rounded-lg shadow-subtle hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 transition-all btn-tactile active:scale-[0.98]">
                Save Auto-Sleep Settings
            </button>
        </form>
    </section>
    
    <!-- Footer Spacer -->
    <div class="h-8"></div>

