<?php
$endpoint = public_url('api/send.php');
$showTestPage = current_user() && app_config('app.enable_test_page') && is_local_request();
?>

<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 pb-5">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">REST API v1.0</span>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800 border border-blue-200">Single API Key</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">API Documentation & Integration Guide</h1>
            <p class="text-[13px] text-slate-500 mt-1 font-medium">Send WhatsApp messages, monitor delivery queues, and handle webhooks using our developer-friendly REST API.</p>
        </div>
        <?php if ($showTestPage): ?>
            <a href="<?= e(base_url('test-page.php')) ?>" class="px-4 py-2 text-[13px] font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg shadow-sm hover:bg-slate-50 transition-all inline-flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                Open Test Page
            </a>
        <?php endif; ?>
    </div>

    <!-- Quick Overview Card -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center mb-3">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
            </div>
            <h3 class="text-sm font-bold text-slate-900">Single Key Auth</h3>
            <p class="text-xs text-slate-500 mt-1">Authenticate requests using only a single <code class="bg-slate-100 px-1 py-0.5 rounded text-slate-700">X-API-Key</code> header. No secondary secret needed.</p>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center mb-3">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            </div>
            <h3 class="text-sm font-bold text-slate-900">Background Worker</h3>
            <p class="text-xs text-slate-500 mt-1">Messages are buffered into an async priority queue and sent through Baileys with humanized anti-ban delays.</p>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center mb-3">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <h3 class="text-sm font-bold text-slate-900">Real-Time Webhooks</h3>
            <p class="text-xs text-slate-500 mt-1">Receive automated HTTPS callbacks when messages are dispatched, delivered, or failed.</p>
        </div>
    </div>

    <!-- API Endpoint Section -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div class="flex items-center gap-3">
                <span class="px-2.5 py-1 text-xs font-bold text-white bg-slate-900 rounded-md">POST</span>
                <span class="font-mono text-sm font-semibold text-slate-800 break-all"><?= e($endpoint) ?></span>
            </div>
            <span class="text-xs text-slate-500 font-medium">Content-Type: application/json</span>
        </div>

        <div class="p-6 space-y-6">
            <!-- Headers -->
            <div>
                <h3 class="text-sm font-bold text-slate-900 mb-2">Authentication Headers</h3>
                <div class="bg-slate-900 text-slate-200 p-4 rounded-xl font-mono text-xs overflow-x-auto shadow-inner">
                    <span class="text-slate-400"># Pass your API Key in either the X-API-Key header or Bearer Token:</span><br>
                    <span class="text-emerald-400">X-API-Key:</span> wa_live_YOUR_API_KEY_HERE<br>
                    <span class="text-emerald-400">Content-Type:</span> application/json
                </div>
            </div>

            <!-- Parameters Table -->
            <div>
                <h3 class="text-sm font-bold text-slate-900 mb-2">Request Body (JSON)</h3>
                <div class="overflow-x-auto border border-slate-200 rounded-xl">
                    <table class="min-w-full divide-y divide-slate-200 text-xs">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-2.5 text-left font-bold text-slate-700">Field</th>
                                <th class="px-4 py-2.5 text-left font-bold text-slate-700">Type</th>
                                <th class="px-4 py-2.5 text-left font-bold text-slate-700">Required</th>
                                <th class="px-4 py-2.5 text-left font-bold text-slate-700">Description</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white font-medium">
                            <tr>
                                <td class="px-4 py-3 font-mono font-bold text-slate-900">to</td>
                                <td class="px-4 py-3 text-slate-500">string</td>
                                <td class="px-4 py-3"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">Required</span></td>
                                <td class="px-4 py-3 text-slate-600">Recipient's phone number with country code (e.g. <code class="bg-slate-100 px-1 py-0.5 rounded">919876543210</code>) or 10-digit Indian number.</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-mono font-bold text-slate-900">message</td>
                                <td class="px-4 py-3 text-slate-500">string</td>
                                <td class="px-4 py-3"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">Required</span></td>
                                <td class="px-4 py-3 text-slate-600">The message content. Supports WhatsApp formatting (<code class="bg-slate-100 px-1 py-0.5 rounded">*bold*</code>, <code class="bg-slate-100 px-1 py-0.5 rounded">_italic_</code>, <code class="bg-slate-100 px-1 py-0.5 rounded">~strike~</code>).</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-mono font-bold text-slate-900">humanize</td>
                                <td class="px-4 py-3 text-slate-500">boolean</td>
                                <td class="px-4 py-3"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">Optional</span></td>
                                <td class="px-4 py-3 text-slate-600">Defaults to <code class="bg-slate-100 px-1 py-0.5 rounded">true</code>. Adds micro-delays between messages to simulate human typing and prevent spam bans.</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-mono font-bold text-slate-900">campaign_id</td>
                                <td class="px-4 py-3 text-slate-500">integer</td>
                                <td class="px-4 py-3"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">Optional</span></td>
                                <td class="px-4 py-3 text-slate-600">Optional campaign ID to group analytics and delivery metrics in your dashboard.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Code Examples Section -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
            <h2 class="text-base font-bold text-slate-900">Code Examples (Single API Key)</h2>
            <p class="text-xs text-slate-500 mt-0.5">Copy-paste ready snippets in multiple programming languages.</p>
        </div>

        <div class="p-6 space-y-6">
            <!-- cURL -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> cURL (Terminal / Bash)
                    </span>
                </div>
                <pre class="bg-slate-900 text-slate-200 p-4 rounded-xl font-mono text-xs overflow-x-auto shadow-inner leading-relaxed"><code>curl -X POST "<?= e($endpoint) ?>" \
  -H "Content-Type: application/json" \
  -H "X-API-Key: YOUR_API_KEY_HERE" \
  -d '{
    "to": "919876543210",
    "message": "Hello from WhatsApp API Hub! 🚀",
    "humanize": true
  }'</code></pre>
            </div>

            <!-- Python -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span> Python (requests)
                    </span>
                </div>
                <pre class="bg-slate-900 text-slate-200 p-4 rounded-xl font-mono text-xs overflow-x-auto shadow-inner leading-relaxed"><code>import requests

url = "<?= e($endpoint) ?>"
headers = {
    "Content-Type": "application/json",
    "X-API-Key": "YOUR_API_KEY_HERE"
}
payload = {
    "to": "919876543210",
    "message": "Hello from Python! 🐍",
    "humanize": True
}

response = requests.post(url, json=payload, headers=headers, timeout=15)
print(response.json())</code></pre>
            </div>

            <!-- JavaScript / Node.js -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span> JavaScript / Node.js (fetch)
                    </span>
                </div>
                <pre class="bg-slate-900 text-slate-200 p-4 rounded-xl font-mono text-xs overflow-x-auto shadow-inner leading-relaxed"><code>const response = await fetch("<?= e($endpoint) ?>", {
  method: "POST",
  headers: {
    "Content-Type": "application/json",
    "X-API-Key": "YOUR_API_KEY_HERE"
  },
  body: JSON.stringify({
    to: "919876543210",
    message: "Order confirmed via Node.js! ⚡",
    humanize: true
  })
});

const data = await response.json();
console.log(data);</code></pre>
            </div>

            <!-- PHP -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span> PHP (cURL)
                    </span>
                </div>
                <pre class="bg-slate-900 text-slate-200 p-4 rounded-xl font-mono text-xs overflow-x-auto shadow-inner leading-relaxed"><code>$payload = [
    'to' => '919876543210',
    'message' => 'Hello from PHP Client! 🐘',
    'humanize' => true
];

$ch = curl_init("<?= e($endpoint) ?>");
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'X-API-Key: YOUR_API_KEY_HERE'
    ],
    CURLOPT_POSTFIELDS => json_encode($payload)
]);

$response = curl_exec($ch);
curl_close($ch);
echo $response;</code></pre>
            </div>

            <!-- Go -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-cyan-500"></span> Go (net/http)
                    </span>
                </div>
                <pre class="bg-slate-900 text-slate-200 p-4 rounded-xl font-mono text-xs overflow-x-auto shadow-inner leading-relaxed"><code>package main

import (
	"bytes"
	"encoding/json"
	"fmt"
	"io"
	"net/http"
)

func main() {
	payload, _ := json.Marshal(map[string]interface{}{
		"to":       "919876543210",
		"message":  "Instant alert via Go! 🐹",
		"humanize": true,
	})

	req, _ := http.NewRequest("POST", "<?= e($endpoint) ?>", bytes.NewBuffer(payload))
	req.Header.Set("Content-Type", "application/json")
	req.Header.Set("X-API-Key", "YOUR_API_KEY_HERE")

	client := &http.Client{}
	resp, _ := client.Do(req)
	defer resp.Body.Close()
	body, _ := io.ReadAll(resp.Body)
	fmt.Println(string(body))
}</code></pre>
            </div>
        </div>
    </div>

    <!-- API Response Formats -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Success Response -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
            <div class="flex items-center gap-2 mb-3">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                <h3 class="text-sm font-bold text-slate-900">Success Response (HTTP 200 OK)</h3>
            </div>
            <pre class="bg-slate-900 text-emerald-400 p-4 rounded-xl font-mono text-xs overflow-x-auto shadow-inner leading-relaxed"><code>{
  "ok": true,
  "status": "queued",
  "message_id": 142,
  "request_id": "req_671e8845f7b67",
  "recipient": "919876543210"
}</code></pre>
            <p class="text-xs text-slate-500 mt-2">The message is buffered into the worker's priority queue and scheduled for immediate background delivery.</p>
        </div>

        <!-- Error Response -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
            <div class="flex items-center gap-2 mb-3">
                <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                <h3 class="text-sm font-bold text-slate-900">Error Response (HTTP 400 / 401 / 429)</h3>
            </div>
            <pre class="bg-slate-900 text-rose-400 p-4 rounded-xl font-mono text-xs overflow-x-auto shadow-inner leading-relaxed"><code>{
  "ok": false,
  "error": "Invalid API key or key is disabled."
}</code></pre>
            <p class="text-xs text-slate-500 mt-2">Check that your API key is active in your dashboard and that your WhatsApp Web session is currently Online.</p>
        </div>
    </div>

    <!-- Webhooks Documentation -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
            <h2 class="text-base font-bold text-slate-900">Webhooks & Real-Time Delivery Notifications</h2>
            <p class="text-xs text-slate-500 mt-0.5">Configure your HTTPS webhook URL in the Dashboard under Webhooks to receive live delivery status updates.</p>
        </div>
        <div class="p-6">
            <p class="text-xs text-slate-600 mb-3">When a message is sent or changes status, our background worker immediately performs an HTTP POST request to your webhook endpoint:</p>
            <pre class="bg-slate-900 text-slate-200 p-4 rounded-xl font-mono text-xs overflow-x-auto shadow-inner leading-relaxed"><code>{
  "event": "message.delivered",
  "message_id": 142,
  "to": "919876543210",
  "status": "sent",
  "timestamp": "2026-10-13T14:45:00Z"
}</code></pre>
        </div>
    </div>

    <!-- Python Interactive CLI Tool Card -->
    <div class="bg-gradient-to-r from-slate-900 to-slate-800 text-white rounded-xl p-6 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">CLI Tool Included</span>
                <span class="text-xs text-slate-400">Interactive Terminal</span>
            </div>
            <h3 class="text-base font-bold text-white">Send Messages Directly from Terminal</h3>
            <p class="text-xs text-slate-300 mt-1 max-w-xl">Use our interactive Python program to send messages by prompting for phone number and text directly in PowerShell or Terminal.</p>
        </div>
        <div class="bg-slate-950 px-4 py-2.5 rounded-lg border border-slate-700 font-mono text-xs text-emerald-400 select-all shrink-0">
            python send_whatsapp.py
        </div>
    </div>

    <!-- Author & Legal Notice -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <h3 class="text-sm font-bold text-slate-900 mb-1">Author & Legal Notice</h3>
        <p class="text-xs text-slate-500 leading-relaxed">
            Developed by <strong><a href="https://github.com/navneetyadav-code" target="_blank" rel="noopener noreferrer" class="text-slate-800 font-semibold hover:underline">Navneet Kumar Yadav</a></strong>.
            This project is an independent, community-driven open-source automation library and is not affiliated with, sponsored by, or endorsed by Meta Platforms, Inc. or WhatsApp Inc.
            <br><br>
            For any copyright, removal, or takedown requests, contact: <a href="mailto:info.assistgroupofficial@gmail.com" class="text-brand-wa_med font-semibold hover:underline">info.assistgroupofficial@gmail.com</a>.
        </p>
    </div>
</div>
