    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Webhooks & Event Callbacks</h1>
            <p class="text-[13px] text-slate-500 mt-0.5 font-medium">Stream real-time message delivery receipts (sent, delivered, failed) to your server endpoint.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-white border border-slate-200/80 text-slate-700 shadow-subtle">
                <span class="w-1.5 h-1.5 rounded-full <?= $webhookEnabled ? 'bg-emerald-500' : 'bg-slate-400' ?>"></span>
                <span><?= $webhookEnabled ? 'Delivery Active' : 'Delivery Disabled' ?></span>
            </span>
        </div>
    </div>

    <!-- Integrated Telemetry Ribbon -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-subtle mb-6 overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-slate-100">
            <div class="p-4 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-semibold text-slate-500 tracking-tight">Endpoint Dispatch Status</div>
                    <div class="text-xl font-bold <?= $webhookEnabled ? 'text-emerald-600' : 'text-slate-900' ?> tracking-tight mt-0.5"><?= $webhookEnabled ? 'Enabled' : 'Disabled' ?></div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Asynchronous HTTP POST delivery</div>
                </div>
                <div class="w-8 h-8 rounded-lg <?= $webhookEnabled ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-slate-50 text-slate-500 border border-slate-100' ?> flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
            </div>
            <div class="p-4 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-semibold text-slate-500 tracking-tight">Cryptographic Signing</div>
                    <div class="text-xl font-bold text-slate-900 tracking-tight mt-0.5"><?= !empty($webhook['secret']) ? 'HMAC-SHA256' : 'Unsigned' ?></div>
                    <div class="text-[11px] text-slate-400 mt-0.5">X-Hub-Signature validation</div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-500 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </div>
            </div>
            <div class="p-4 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-semibold text-slate-500 tracking-tight">Subscribed Event Types</div>
                    <div class="text-xl font-bold text-slate-900 tracking-tight mt-0.5">3 Events</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">sent &bull; delivered &bull; failed</div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-500 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Webhook Settings & Reveal Deck -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <section class="lg:col-span-2 bg-white rounded-xl border border-slate-200/80 shadow-card p-5 sm:p-6">
            <h2 class="text-[14px] font-bold text-slate-900 tracking-tight mb-1">Webhook Endpoint Configuration</h2>
            <p class="text-[12px] text-slate-500 mb-5">Provide a publicly accessible HTTPS endpoint to receive webhook payloads.</p>
            
            <?php if ($newWebhookSecret): ?>
                <div class="mb-5 p-4 bg-emerald-50/80 border border-emerald-200 rounded-lg">
                    <div class="text-[12px] font-bold text-emerald-950 mb-1">New Webhook Signing Secret Generated</div>
                    <input readonly value="<?= e($newWebhookSecret) ?>" onclick="this.select()" class="block w-full px-3 py-2 text-[12px] border border-emerald-300 rounded-md bg-white text-slate-800 font-mono shadow-subtle cursor-copy" title="Click to copy secret">
                    <p class="text-[10px] text-emerald-700 mt-1">Copy this secret now to verify incoming HMAC signatures.</p>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= e(base_url('index.php?page=webhook')) ?>" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                
                <div>
                    <label class="block text-[12px] font-semibold text-slate-700 mb-1">Webhook Target URL (HTTPS)</label>
                    <input name="target_url" placeholder="https://example.com/api/webhook" value="<?= e($webhook['target_url'] ?? '') ?>" class="block w-full px-3 py-2 text-[12px] border border-slate-200 rounded-lg bg-white shadow-subtle focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 font-mono">
                </div>

                <div>
                    <label class="block text-[12px] font-semibold text-slate-700 mb-1">Signing Secret (HMAC-SHA256)</label>
                    <input name="secret" placeholder="Webhook signing secret" value="<?= e($webhook['secret'] ?? '') ?>" class="block w-full px-3 py-2 text-[12px] border border-slate-200 rounded-lg bg-white shadow-subtle focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 font-mono">
                </div>

                <div class="space-y-2 pt-2 border-t border-slate-100">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input name="rotate_secret" type="checkbox" class="w-4 h-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900 cursor-pointer">
                        <span class="text-[12px] font-semibold text-slate-700">Rotate signing secret on save</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input name="enabled" type="checkbox" <?= !empty($webhook['enabled']) ? 'checked' : '' ?> class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer">
                        <span class="text-[12px] font-semibold text-slate-700">Enable automated webhook dispatch</span>
                    </label>
                </div>

                <div class="pt-3">
                    <button type="submit" class="px-4 py-2 text-[12px] font-semibold text-white bg-slate-900 rounded-lg shadow-subtle hover:bg-slate-800 transition-all btn-tactile active:scale-[0.98]">
                        Save Webhook Settings
                    </button>
                </div>
            </form>
        </section>

        <!-- Webhook Payload Schema Reference -->
        <section class="bg-white rounded-xl border border-slate-200/80 shadow-card p-5">
            <h2 class="text-[14px] font-bold text-slate-900 tracking-tight mb-1">Payload Schema</h2>
            <p class="text-[12px] text-slate-500 mb-3">Sample JSON dispatched to your endpoint:</p>
            <div class="bg-slate-900 rounded-lg p-3 text-[11px] font-mono text-slate-300 overflow-x-auto leading-relaxed border border-slate-800">
<pre>{
  <span class="text-emerald-400">"event"</span>: <span class="text-amber-300">"message.sent"</span>,
  <span class="text-emerald-400">"message_id"</span>: <span class="text-cyan-300">1042</span>,
  <span class="text-emerald-400">"recipient"</span>: <span class="text-amber-300">"+919876543210"</span>,
  <span class="text-emerald-400">"status"</span>: <span class="text-amber-300">"sent"</span>,
  <span class="text-emerald-400">"timestamp"</span>: <span class="text-amber-300">"<?= date('c') ?>"</span>
}</pre>
            </div>
            <p class="text-[10px] text-slate-400 mt-2.5">Incoming requests contain header: <code>X-WhatsGateway-Signature</code></p>
        </section>
    </div>

