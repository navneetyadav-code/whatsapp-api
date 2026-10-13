const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
const statusBadge = document.querySelector('#statusBadge');
const qrImage = document.querySelector('#qrImage');
const qrPlaceholder = document.querySelector('#qrPlaceholder');
const phoneText = document.querySelector('#phoneText');
const nameText = document.querySelector('#nameText');
const connectBtn = document.querySelector('#connectBtn');
const disconnectBtn = document.querySelector('#disconnectBtn');
const testForm = document.querySelector('#testForm');
const testResult = document.querySelector('#testResult');

async function callSession(action, options = {}) {
    const requestUrl = `session.php?action=${encodeURIComponent(action)}`;
    const requestOptions = {
        method: options.method || 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrf,
        },
        body: options.body ? JSON.stringify(options.body) : undefined,
    };

    let response;
    if (typeof fetch === 'function') {
        response = await fetch(requestUrl, requestOptions);
    } else {
        response = await xhrJson(requestUrl, requestOptions);
    }

    const data = await response.json();
    if (!response.ok || data.ok === false) {
        throw new Error(data.error || `Session request failed (${response.status})`);
    }
    return data;
}

function xhrJson(url, options) {
    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        xhr.open(options.method || 'GET', url, true);
        Object.entries(options.headers || {}).forEach(([key, value]) => xhr.setRequestHeader(key, value));
        xhr.onload = () => resolve({
            ok: xhr.status >= 200 && xhr.status < 300,
            status: xhr.status,
            json: async () => {
                try {
                    return JSON.parse(xhr.responseText || '{}');
                } catch (error) {
                    return { ok: false, error: xhr.responseText || 'Invalid worker response.' };
                }
            },
        });
        xhr.onerror = () => reject(new Error('Network error while contacting the worker.'));
        xhr.send(options.body);
    });
}

function setDeviceActionMessage(message, type = 'info') {
    const qrPlaceholder = document.getElementById('qrPlaceholder');
    const span = qrPlaceholder?.querySelector('span');
    if (span) {
        span.textContent = message;
        span.className = 'text-[12px] font-medium max-w-[190px] leading-relaxed ' + (type === 'error' ? 'text-rose-600' : 'text-slate-500');
    }
    if (type === 'error' && typeof window.showToast === 'function') {
        window.showToast(message, 'error');
    }
}

function renderStatus(data) {
    const session = data.session || {};
    const state = session.state || 'idle';
    const isSleeping = Boolean(session.sleeping);
    const hasLinkedDevice = state === 'connected' || (isSleeping && Boolean(session.phone || session.pushName));

    const statusBadge = document.getElementById('statusBadge');
    const statusIndicator = document.getElementById('statusIndicator');
    const statusText = document.getElementById('statusText');
    const topPhoneText = document.getElementById('topPhoneText');
    const phoneText = document.getElementById('phoneText');
    const nameText = document.getElementById('nameText');
    const lastConnectedText = document.getElementById('lastConnectedText');
    
    const connectBtn = document.getElementById('connectBtn');
    const disconnectBtn = document.getElementById('disconnectBtn');
    const resetBtn = document.getElementById('resetBtn');
    const sleepBtn = document.getElementById('sleepBtn');
    const wakeupBtn = document.getElementById('wakeupBtn');
    
    const profileContainer = document.getElementById('profileContainer');
    const qrContainer = document.getElementById('qrContainer');
    const profileImage = document.getElementById('profileImage');
    const profileName = document.getElementById('profileName');
    
    const qrImage = document.getElementById('qrImage');
    const qrPlaceholder = document.getElementById('qrPlaceholder');
    const lastErrorPanel = document.getElementById('lastErrorPanel');
    const lastErrorText = document.getElementById('lastErrorText');

    // Overview Stats Update
    if (statusText) statusText.textContent = hasLinkedDevice ? (isSleeping ? 'Sleeping' : 'Online') : 'Offline';
    if (topPhoneText) topPhoneText.textContent = session.phone || 'None';
    if (statusIndicator) {
        const pings = statusIndicator.querySelectorAll('span');
        pings.forEach(p => {
            p.className = p.className.replace(/bg-\w+-\d+/g, hasLinkedDevice ? (isSleeping ? 'bg-amber-500' : 'bg-emerald-500') : 'bg-slate-500');
        });
    }

    // Detail Panel Update
    if (statusBadge) {
        statusBadge.textContent = isSleeping && hasLinkedDevice ? 'sleeping' : state;
        statusBadge.className = 'px-2.5 py-1 text-[11px] font-semibold rounded-full border shadow-sm flex items-center gap-1.5 ' + (hasLinkedDevice ? (isSleeping ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200') : 'bg-slate-100 text-slate-700 border-slate-200');
    }
    
    if (phoneText) phoneText.textContent = session.phone || 'Not connected';
    if (nameText) nameText.textContent = session.pushName || 'Unknown';
    if (profileName) profileName.textContent = session.pushName || 'Connected';
    if (lastConnectedText && session.connected_at) lastConnectedText.textContent = session.connected_at;
    if (lastErrorPanel && lastErrorText) {
        if (session.error) {
            lastErrorText.textContent = session.error;
            lastErrorPanel.classList.remove('hidden');
        } else {
            lastErrorText.textContent = '';
            lastErrorPanel.classList.add('hidden');
        }
    }

    // View Toggles
    if (hasLinkedDevice) {
        if (profileContainer) profileContainer.classList.remove('hidden');
        if (qrContainer) qrContainer.classList.add('hidden');
        
        // Buttons
        if (connectBtn) connectBtn.classList.add('hidden');
        if (disconnectBtn) disconnectBtn.classList.remove('hidden'); if(resetBtn) resetBtn.classList.remove('hidden');
        if (sleepBtn) isSleeping ? sleepBtn.classList.add('hidden') : sleepBtn.classList.remove('hidden');
        if (wakeupBtn) !isSleeping ? wakeupBtn.classList.add('hidden') : wakeupBtn.classList.remove('hidden');

        // Profile Image
        if (profileImage && session.profilePicture) {
            profileImage.src = session.profilePicture;
        } else if (profileImage) {
            profileImage.src = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(session.pushName || 'W') + '&background=0D8ABC&color=fff';
        }
    } else {
        if (profileContainer) profileContainer.classList.add('hidden');
        if (qrContainer) qrContainer.classList.remove('hidden');
        
        // Buttons
        if (connectBtn) connectBtn.classList.remove('hidden');
        if (disconnectBtn) disconnectBtn.classList.add('hidden');
        if (resetBtn) {
            if (['qr', 'disconnected', 'error', 'connecting'].includes(state) || Boolean(session.error)) {
                resetBtn.classList.remove('hidden');
            } else {
                resetBtn.classList.add('hidden');
            }
        }
        if (sleepBtn) sleepBtn.classList.add('hidden');
        if (wakeupBtn) wakeupBtn.classList.add('hidden');

        // QR Code Handling
        if (session.qr && qrImage && qrPlaceholder) {
            qrImage.src = session.qr;
            qrImage.classList.remove('hidden');
            qrPlaceholder.classList.add('hidden');
        } else if (qrImage && qrPlaceholder) {
            qrImage.classList.add('hidden');
            qrPlaceholder.classList.remove('hidden');
            const span = qrPlaceholder.querySelector('span');
            if (span) {
                if (session.error) {
                    span.textContent = session.error;
                } else if (state === 'connecting') {
                    span.textContent = 'Generating QR code...';
                } else if (state === 'qr') {
                    span.textContent = 'Waiting for QR code...';
                } else {
                    span.textContent = 'Click Connect to generate QR code';
                }
            }
        }
    }
}

async function refreshStatus() {
    try {
        renderStatus(await callSession('status'));
    } catch (error) {
        if (statusBadge) {
            statusBadge.textContent = 'worker off';
            statusBadge.className = 'device-state-badge error';
        }
        setDeviceActionMessage(error.message || 'Worker is not reachable.', 'error');
    }
}

async function runDeviceAction(action, button, message) {
    if (!button) return;
    button.disabled = true;
    setDeviceActionMessage(message);
    try {
        renderStatus(await callSession(action, { method: 'POST' }));
    } catch (error) {
        setDeviceActionMessage(error.message || 'Could not update WhatsApp session.', 'error');
    } finally {
        button.disabled = false;
    }
}

document.addEventListener('click', (event) => {
    const actionButton = event.target.closest('[data-session-action]');
    if (!actionButton) return;

    event.preventDefault();
    const action = actionButton.dataset.sessionAction;
    if (action === 'logout' && actionButton.dataset.confirm && !confirm(actionButton.dataset.confirm)) {
        return;
    }

    const messages = {
        start: 'Generating QR code...',
        logout: 'Logging out device...',
        sleep: 'Putting connection to sleep...',
        wake: 'Waking up connection...',
    };
    runDeviceAction(action, actionButton, messages[action] || 'Updating WhatsApp session...');
});

testForm?.addEventListener('submit', async (event) => {
    event.preventDefault();
    const form = new FormData(testForm);
    testResult.textContent = 'Sending...';

    try {
        const result = await callSession('send-test', {
            method: 'POST',
            body: {
                to: form.get('to'),
                message: form.get('message'),
            },
        });
        testResult.textContent = result.ok
            ? `Message queued as #${result.message_id}. The worker will send it in the background.`
            : (result.error || 'Message failed.');
    } catch (error) {
        testResult.textContent = 'Could not reach the worker.';
    }
});

refreshStatus();
setInterval(refreshStatus, 3000);


// Immediate execution for UI Toggles (since script is at end of body)
const initUIToggles = () => {
    const messageModeSelect = document.querySelector('[data-message-mode-select]');
    if (messageModeSelect) {
        // Trigger initially in case it's not the default
        const initialMode = messageModeSelect.value;
        document.querySelectorAll('[data-message-panel]').forEach((panel) => {
            panel.hidden = panel.getAttribute('data-message-panel') !== initialMode;
        });

        messageModeSelect.addEventListener('change', function(e) {
            const selectedMode = e.target.value;
            document.querySelectorAll('[data-message-panel]').forEach((panel) => {
                panel.hidden = panel.getAttribute('data-message-panel') !== selectedMode;
            });
        });
    }

    const birthdayModeSelect = document.querySelector('[data-birthday-recipient-select]');
    if (birthdayModeSelect) {
        birthdayModeSelect.addEventListener('change', function(e) {
            const selectedMode = e.target.value;
            document.querySelectorAll('[data-birthday-panel]').forEach((panel) => {
                panel.hidden = panel.getAttribute('data-birthday-panel') !== selectedMode;
            });
        });
    }
};


const initRichTextEditor = () => {
    const editors = document.querySelectorAll('textarea.wa-editor');
    
    editors.forEach(textarea => {
        if (textarea.dataset.initialized) return;
        textarea.dataset.initialized = 'true';
        
        // Create wrapper
        const wrapper = document.createElement('div');
        wrapper.className = 'wa-editor-wrapper bg-white border border-slate-200 rounded-lg shadow-sm focus-within:ring-2 focus-within:ring-brand-wa_light/20 focus-within:border-brand-wa_med overflow-hidden transition-all flex flex-col';
        
        // Ensure textarea has no borders and no ring so it blends with wrapper
        textarea.classList.remove('border', 'border-slate-200', 'rounded-lg', 'focus:ring-2', 'focus:ring-brand-wa_light/20', 'focus:border-brand-wa_med', 'shadow-sm');
        textarea.classList.add('border-0', 'focus:ring-0', 'resize-y', 'flex-1', 'min-h-[100px]');
        if(textarea.classList.contains('mb-6')) {
            wrapper.classList.add('mb-6');
            textarea.classList.remove('mb-6');
        }

        textarea.parentNode.insertBefore(wrapper, textarea);
        
        // Create toolbar
        const toolbar = document.createElement('div');
        toolbar.className = 'wa-toolbar bg-slate-50 border-b border-slate-100 px-2 py-1.5 flex flex-wrap items-center gap-1';
        
        const presets = textarea.dataset.presets ? JSON.parse(textarea.dataset.presets) : [];
        
        // Format buttons
        const formats = [
            { id: 'bold', icon: 'B', tag: '*', title: 'Bold' },
            { id: 'italic', icon: 'I', tag: '_', title: 'Italic' },
            { id: 'strike', icon: 'S', tag: '~', title: 'Strikethrough' },
            { id: 'code', icon: '&lt;&gt;', tag: '`', title: 'Monospace' }
        ];
        
        formats.forEach(f => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'w-7 h-7 rounded flex items-center justify-center text-slate-600 hover:bg-slate-200 hover:text-slate-900 transition-colors font-bold text-[13px]';
            if (f.id === 'italic') btn.className += ' italic';
            if (f.id === 'strike') btn.className += ' line-through';
            if (f.id === 'code') btn.className += ' font-mono text-[10px]';
            
            btn.innerHTML = f.icon;
            btn.title = f.title;
            
            btn.addEventListener('click', () => {
                const start = textarea.selectionStart;
                const end = textarea.selectionEnd;
                const selected = textarea.value.substring(start, end);
                
                const before = textarea.value.substring(0, start);
                const after = textarea.value.substring(end);
                
                textarea.value = before + f.tag + selected + f.tag + after;
                textarea.focus();
                
                // Reposition cursor inside tags
                const newPos = start + f.tag.length + selected.length;
                textarea.setSelectionRange(newPos, newPos);
            });
            
            toolbar.appendChild(btn);
        });
        
        // Add divider if we have presets
        if (presets.length > 0) {
            const div = document.createElement('div');
            div.className = 'w-px h-4 bg-slate-300 mx-1';
            toolbar.appendChild(div);
            
            presets.forEach(p => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'px-2 py-1 rounded text-[11px] font-semibold text-slate-600 bg-white border border-slate-200 shadow-sm hover:bg-slate-50 hover:text-brand-wa_med transition-colors';
                btn.textContent = p;
                btn.title = 'Insert ' + p;
                
                btn.addEventListener('click', () => {
                    const start = textarea.selectionStart;
                    const end = textarea.selectionEnd;
                    const before = textarea.value.substring(0, start);
                    const after = textarea.value.substring(end);
                    
                    textarea.value = before + p + after;
                    textarea.focus();
                    const newPos = start + p.length;
                    textarea.setSelectionRange(newPos, newPos);
                });
                
                toolbar.appendChild(btn);
            });
        }
        
        wrapper.appendChild(toolbar);
        wrapper.appendChild(textarea);
    });
};


function insertFormatting(textarea, prefix, suffix) {
    textarea.focus();
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    
    if (start !== end) {
        // Text is selected, wrap it
        const selected = text.substring(start, end);
        const before = text.substring(0, start);
        const after = text.substring(end);
        textarea.value = before + prefix + selected + suffix + after;
        textarea.selectionStart = start + prefix.length;
        textarea.selectionEnd = end + prefix.length;
    } else {
        // No text selected, insert prefix and suffix and place cursor between them
        const before = text.substring(0, start);
        const after = text.substring(start);
        textarea.value = before + prefix + suffix + after;
        textarea.selectionStart = textarea.selectionEnd = start + prefix.length;
    }
    
    // trigger input event so frameworks/alpine know
    textarea.dispatchEvent(new Event('input'));
}

function insertTextAtCursor(textarea, insertText) {
    textarea.focus();
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    const before = text.substring(0, start);
    const after = text.substring(end);
    
    textarea.value = before + insertText + after;
    textarea.selectionStart = textarea.selectionEnd = start + insertText.length;
    textarea.dispatchEvent(new Event('input'));
}


const sleepBtn = document.getElementById('sleepBtn');
const wakeupBtn = document.getElementById('wakeupBtn');

const resetBtn = document.getElementById('resetBtn');



// Animated Toast UI
window.showToast = function(message, type = 'error') {
    const toast = document.createElement('div');
    const isError = type === 'error';
    const borderColor = isError ? 'border-rose-500' : 'border-emerald-500';
    const iconColor = isError ? 'text-rose-500 bg-rose-100' : 'text-emerald-500 bg-emerald-100';
    const svgIcon = isError 
        ? '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
        : '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
    
    toast.className = 'fixed top-4 right-4 z-50 flex items-center w-full max-w-sm p-4 mb-4 text-slate-700 bg-white rounded-xl shadow-2xl border-l-4 transition-all duration-300 transform translate-x-full opacity-0 ' + borderColor;
    
    toast.innerHTML = '<div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-lg ' + iconColor + '">' + svgIcon + '</div><div class="ml-3 text-[13px] font-medium text-slate-700">' + message + '</div>';
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.remove('translate-x-full', 'opacity-0');
    }, 10);
    
    setTimeout(() => {
        toast.classList.add('translate-x-full', 'opacity-0');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
};

// Replace old alert with toast for phone validation
const initPhoneInputs = () => {
    const phoneInputs = document.querySelectorAll('input[name="manual_number"], input[name="phone"], input[name="recipient_phone"]');
    if (phoneInputs.length === 0 || typeof window.intlTelInput !== 'function') return;

    let defaultCountry = (window.AppConfig?.defaultCountryCode || "in").toLowerCase();
    
    // If the user provided a numeric dial code (like '91' instead of 'in')
    if (/^\d+$/.test(defaultCountry)) {
        if (typeof window.intlTelInputGlobals !== 'undefined') {
            const countries = window.intlTelInputGlobals.getCountryData();
            const match = countries.find(c => c.dialCode === defaultCountry);
            if (match) defaultCountry = match.iso2;
        } else {
            // Fallback common mappings if globals not available
            const map = { '91': 'in', '1': 'us', '44': 'gb', '61': 'au', '971': 'ae', '92': 'pk' };
            defaultCountry = map[defaultCountry] || 'in';
        }
    }

    phoneInputs.forEach(input => {
        const iti = window.intlTelInput(input, {
            initialCountry: defaultCountry, 
            utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.10/build/js/utils.js",
            separateDialCode: true,
            nationalMode: true
        });
        input._iti = iti;

        const form = input.closest('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (input.value.trim() !== '' && input.offsetParent !== null) {
                    if (!iti.isValidNumber()) {
                        e.preventDefault();
                        const errorMap = ["Invalid number", "Invalid country code", "Too short", "Too long", "Invalid number"];
                        const errorCode = iti.getValidationError();
                        window.showToast("Phone number error: " + (errorMap[errorCode] || "Invalid number"), "error");
                        input.focus();
                        return false;
                    }
                    input.value = iti.getNumber();
                }
            });
        }
    });
};

// Compose Message Form AJAX Logic
const composeForm = document.querySelector('form[action*="message-send"]');
if (composeForm) {
    composeForm.addEventListener('submit', async (e) => {
        if (e.defaultPrevented) return;
        
        const recipientMode = composeForm.querySelector('[name="recipient_mode"]')?.value;
        if (recipientMode === 'number') {
            const manualInput = composeForm.querySelector('input[name="manual_number"]');
            if (manualInput && manualInput.value.trim() !== '') {
                if (manualInput._iti) {
                    if (!manualInput._iti.isValidNumber()) {
                        const errorMap = ["Invalid number", "Invalid country code", "Too short", "Too long", "Invalid number"];
                        const errorCode = manualInput._iti.getValidationError();
                        window.showToast("Phone number error: " + (errorMap[errorCode] || "Invalid number"), "error");
                        manualInput.focus();
                        e.preventDefault();
                        return;
                    }
                    manualInput.value = manualInput._iti.getNumber();
                }
            }
        }

        e.preventDefault();
        
        const submitBtn = composeForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 w-4 h-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Queuing...';
        
        try {
            const formData = new FormData(composeForm);
            
            const response = await fetch(composeForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const result = await response.json();
            
            if (response.ok && result.ok) {
                window.showToast(result.message || 'Message successfully queued!', 'success');
                composeForm.reset();
                const status = document.getElementById('attachmentStatus'); if (status) status.textContent = 'No file selected';
                const editor = composeForm.querySelector('textarea.wa-editor');
                if(editor) editor.value = '';
            } else {
                window.showToast(result.error || 'Failed to queue message.', 'error');
            }
        } catch (error) {
            window.showToast('Network error while queueing message.', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
}
// Initialize all features safely
const initAttachmentUI = () => {
    const input = document.getElementById('messageAttachment');
    const status = document.getElementById('attachmentStatus');
    if (input && status) {
        input.addEventListener('change', function() {
            if (this.files && this.files.length > 0) {
                status.textContent = this.files[0].name;
            } else {
                status.textContent = 'No file selected';
            }
        });
    }
};

// Prevent virtual keyboard from shifting the website header/layout upwards on mobile
const initMobileViewportKeyboardHandler = () => {
    const resetWindowScroll = () => {
        if (window.scrollY !== 0) {
            window.scrollTo(0, 0);
        }
    };

    if (window.visualViewport) {
        window.visualViewport.addEventListener('resize', resetWindowScroll);
        window.visualViewport.addEventListener('scroll', resetWindowScroll);
    }

    window.addEventListener('scroll', resetWindowScroll, { passive: true });

    // When focusing input/textarea on mobile, ensure smooth containment inside workspace
    document.addEventListener('focusin', (e) => {
        if (['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) {
            setTimeout(() => {
                resetWindowScroll();
                const workspace = document.getElementById('workspaceContent') || e.target.closest('.overflow-auto') || e.target.closest('.overflow-y-auto');
                if (workspace && typeof e.target.scrollIntoView === 'function') {
                    e.target.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            }, 120);
        }
    });

    document.addEventListener('focusout', (e) => {
        if (['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) {
            setTimeout(resetWindowScroll, 80);
        }
    });
};
// Queue Live Polling (Real-time updates without page reload)
const initQueueLivePolling = () => {
    const tableBody = document.getElementById('liveQueueTable');
    const recentBody = document.getElementById('recentQueueTable');
    if (!tableBody && !recentBody) return;

    const queuedBadge = document.getElementById('liveQueuedCount');
    const processingBadge = document.getElementById('liveProcessingCount');
    const failedBadge = document.getElementById('liveQueueFailedCount');

    const statusColors = {
        'queued': 'bg-slate-100 text-slate-700 border-slate-200',
        'processing': 'bg-blue-50 text-blue-700 border-blue-200',
        'sent': 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'failed': 'bg-rose-50 text-rose-700 border-rose-200',
        'cancelled': 'bg-orange-50 text-orange-700 border-orange-200'
    };
    const dotColors = {
        'queued': 'bg-slate-500',
        'processing': 'bg-blue-500 animate-pulse',
        'sent': 'bg-emerald-500',
        'failed': 'bg-rose-500',
        'cancelled': 'bg-orange-500'
    };

    let isPolling = false;

    const updateQueue = async () => {
        if (isPolling) return;
        isPolling = true;

        try {
            const urlParams = new URLSearchParams(window.location.search);
            const currentPage = urlParams.get('p') || '1';

            const res = await fetch(`session.php?action=queue-status&p=${encodeURIComponent(currentPage)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) return;

            const data = await res.json();
            if (!data.ok) return;

            // Update counter badges
            if (queuedBadge && data.stats) queuedBadge.textContent = data.stats.queued;
            if (processingBadge && data.stats) processingBadge.textContent = data.stats.processing;
            if (failedBadge && data.stats) failedBadge.textContent = data.stats.failed;

            // Update full queue table
            if (tableBody) {
                if (!data.messages || data.messages.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="7" class="px-6 py-12 text-center text-[13px] text-slate-500">The queue is empty. New API requests will appear here.</td></tr>';
                } else {
                    const rowsHtml = data.messages.map(msg => {
                        const sc = statusColors[msg.status] || 'bg-slate-100 text-slate-700 border-slate-200';
                        const dc = dotColors[msg.status] || 'bg-slate-500';
                        const capStatus = msg.status.charAt(0).toUpperCase() + msg.status.slice(1);
                        const safeBody = (msg.body || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
                        const previewBody = safeBody.length > 60 ? safeBody.substring(0, 60) + '...' : safeBody;
                        const safeError = (msg.error_message || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');

                        let actions = '';
                        if (msg.status === 'queued') {
                            actions = `<form method="post" action="index.php?page=message-cancel">
                                <input type="hidden" name="csrf_token" value="${data.csrf_token || ''}">
                                <input type="hidden" name="id" value="${msg.id}">
                                <button type="submit" class="px-2.5 py-1.5 text-[12px] font-semibold text-rose-600 bg-white border border-rose-200 rounded hover:bg-rose-50 transition-colors shadow-sm">Cancel</button>
                            </form>`;
                        } else if (['failed', 'cancelled'].includes(msg.status)) {
                            actions = `<form method="post" action="index.php?page=message-retry">
                                <input type="hidden" name="csrf_token" value="${data.csrf_token || ''}">
                                <input type="hidden" name="id" value="${msg.id}">
                                <button type="submit" class="px-2.5 py-1.5 text-[12px] font-semibold text-slate-600 bg-white border border-slate-200 rounded hover:bg-slate-50 transition-colors shadow-sm">Retry</button>
                            </form>`;
                        }

                        return `<tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4 text-[13px] font-mono text-slate-500">#${msg.id}</td>
                            <td class="px-6 py-4 text-[13px] font-medium text-slate-900">${msg.recipient}</td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-[11px] font-semibold rounded-full border shadow-sm flex inline-flex items-center gap-1.5 ${sc}">
                                    <span class="w-1.5 h-1.5 rounded-full ${dc}"></span>
                                    ${capStatus}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-[13px] text-slate-600 font-mono">${msg.attempts}/${msg.max_attempts}</td>
                            <td class="px-6 py-4 text-[13px] text-slate-600 truncate max-w-xs" title="${safeBody}">${previewBody}</td>
                            <td class="px-6 py-4 text-[12px] text-rose-600 max-w-xs truncate" title="${safeError}">${safeError}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">${actions}</div>
                            </td>
                        </tr>`;
                    }).join('');

                    tableBody.innerHTML = rowsHtml;
                }
            }

            // Update dashboard recent activity table
            if (recentBody) {
                if (!data.messages || data.messages.length === 0) {
                    recentBody.innerHTML = '<tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">No messages have been queued yet.</td></tr>';
                } else {
                    recentBody.innerHTML = data.messages.slice(0, 5).map(msg => {
                        let badge = '';
                        if (msg.status === 'sent') {
                            badge = '<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60 shadow-sm"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Sent</span>';
                        } else if (msg.status === 'failed') {
                            badge = '<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200/60 shadow-sm"><span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Failed</span>';
                        } else if (msg.status === 'queued' || msg.status === 'processing') {
                            badge = '<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200/60 shadow-sm"><span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Pending</span>';
                        } else {
                            badge = `<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200/60 shadow-sm"><span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> ${msg.status.charAt(0).toUpperCase() + msg.status.slice(1)}</span>`;
                        }
                        const safeBody = (msg.body || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
                        const previewBody = safeBody.length > 40 ? safeBody.substring(0, 40) + '...' : safeBody;
                        const time = msg.created_at ? msg.created_at.substring(11, 19) : '';

                        return `<tr class="hover:bg-slate-50/80 transition-colors group cursor-default">
                            <td class="px-5 py-3">${badge}</td>
                            <td class="px-5 py-3"><div class="flex items-center gap-2.5"><span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200/60 shadow-sm">MSG</span><span class="font-mono text-slate-700 text-[12px] tracking-tight">Outgoing</span></div></td>
                            <td class="px-5 py-3 text-slate-600 font-mono text-[12px] tracking-tight">${msg.recipient}</td>
                            <td class="px-5 py-3 text-slate-500 hidden sm:table-cell font-medium truncate max-w-xs">${previewBody}</td>
                            <td class="px-5 py-3 text-right text-slate-400 text-[12px] group-hover:text-slate-600 transition-colors">${time}</td>
                        </tr>`;
                    }).join('');
                }
            }
        } catch (err) {
            // network hiccup silent ignore
        } finally {
            isPolling = false;
        }
    };

    setInterval(updateQueue, 3000);
};

const runInits = () => {
    if (typeof initMobileViewportKeyboardHandler === 'function') initMobileViewportKeyboardHandler();
    if (typeof initAttachmentUI === 'function') initAttachmentUI();
    if (typeof initUIToggles === 'function') initUIToggles();
    if (typeof initPhoneInputs === 'function') initPhoneInputs();
    if (typeof initRichTextEditor === 'function') initRichTextEditor();
    if (typeof initQueueLivePolling === 'function') initQueueLivePolling();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', runInits);
} else {
    runInits();
}
