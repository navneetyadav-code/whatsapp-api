/**
 * WhatsGateway - WaStudio Centralized Message Engine (WYSIWYG, Active Formatting & Clean Minimal UI)
 * Universal Module Definition (UMD) for Browser and Node.js test environments.
 */
(function (root, factory) {
    if (typeof module === 'object' && module.exports) {
        module.exports = factory();
    } else {
        root.WaStudio = factory();
    }
}(typeof self !== 'undefined' ? self : this, function () {
    'use strict';

    // Authored minimalist SVG icons (2.2px uniform stroke, rounded caps)
    const ICONS = {
        bold: '<svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M6 4h8a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"></path><path d="M6 12h9a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"></path></svg>',
        italic: '<svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="4" x2="10" y2="4"></line><line x1="14" y1="20" x2="5" y2="20"></line><line x1="15" y1="4" x2="9" y2="20"></line></svg>',
        strike: '<svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4H9a3 3 0 0 0-2.83 4"></path><path d="M14 12a3.5 3.5 0 0 1 0 7H8a3 3 0 0 1-2.83-2"></path><line x1="4" y1="12" x2="20" y2="12"></line></svg>',
        code: '<svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>',
        quote: '<svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1z"></path><path d="M15 21c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1z"></path></svg>',
        bullet: '<svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>',
        smile: '<svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M8 14s1.5 2 4 2 4-2 4-2"></path><line x1="9" y1="9" x2="9.01" y2="9"></line><line x1="15" y1="9" x2="15.01" y2="9"></line></svg>',
        tag: '<svg class="w-3 h-3 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>',
        edit: '<svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>',
        preview: '<svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>',
        doubleCheck: '<svg class="w-3.5 h-3.5 text-[#53bdeb] inline-block ml-1" viewBox="0 0 16 15" fill="none"><path d="M15.01 3.316l-7.79 7.79-3.21-3.21.71-.71 2.5 2.5 7.08-7.08.71.71zm-4.79 7.79l-.71.71-3.21-3.21.71-.71 3.21 3.21zm-2.5-2.5l-.71.71-3.21-3.21.71-.71 3.21 3.21z" fill="currentColor"/></svg>',
        close: '<svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>'
    };

    // Curated high-frequency WhatsApp emojis
    const EMOJI_CATEGORIES = [
        {
            name: 'Reactions',
            emojis: ['😀', '😂', '😍', '😎', '🥳', '🤔', '😅', '🤩', '🙏', '👍', '👋', '🔥', '❤️', '💯', '👏', '🎉']
        },
        {
            name: 'Business & Sales',
            emojis: ['🚀', '📦', '🎁', '🏷️', '💰', '💳', '📈', '🛍️', '🛒', '⭐', '✨', '💡', '📢', '🎯', '⚡', '🔔']
        },
        {
            name: 'Support & Alerts',
            emojis: ['💬', '📞', '✉️', '📅', '⏱️', '📍', 'ℹ️', '⚠️', '🚨', '✅', '❌', '❓', '🔒', '🛡️', '🌐', '🤝']
        }
    ];

    const WaStudio = {
        version: '2.1.0',
        ICONS: ICONS,
        EMOJI_CATEGORIES: EMOJI_CATEGORIES,

        /**
         * Escape HTML to strictly prevent XSS injections.
         * @param {string} str
         * @returns {string}
         */
        escapeHtml: function (str) {
            if (typeof str !== 'string') return '';
            return str
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        },

        /**
         * Parses WhatsApp markdown syntax into safe, sanitized HTML for chat bubble previews.
         * @param {string} rawText
         * @returns {string} Safe HTML string
         */
        parse: function (rawText) {
            if (!rawText || typeof rawText !== 'string') return '';

            if (rawText.length > 25000) {
                rawText = rawText.slice(0, 25000) + '... [truncated]';
            }

            let text = this.escapeHtml(rawText);

            // 1. Code blocks
            const codeBlocks = [];
            text = text.replace(/```([\s\S]*?)```/g, function (match, codeContent) {
                const id = '___CODEBLOCK_' + codeBlocks.length + '___';
                codeBlocks.push('<pre class="wa-code-block font-mono bg-slate-900/10 text-slate-800 p-2 rounded my-1.5 text-[11px] overflow-x-auto whitespace-pre"><code>' + codeContent + '</code></pre>');
                return id;
            });

            // 2. Inline code
            const inlineCodes = [];
            text = text.replace(/`([^`\n]+)`/g, function (match, inlineContent) {
                const id = '___INLINECODE_' + inlineCodes.length + '___';
                inlineCodes.push('<code class="wa-inline-code font-mono bg-slate-900/10 text-slate-800 px-1 py-0.5 rounded text-[11px]">' + inlineContent + '</code>');
                return id;
            });

            // 3. Quotes & Bullets
            const lines = text.split('\n');
            const processedLines = lines.map(line => {
                const quoteMatch = line.match(/^&gt;\s?(.*)$/);
                if (quoteMatch) {
                    return '<blockquote class="wa-quote border-l-2 border-emerald-600/60 pl-2.5 my-1 text-slate-600 italic text-[12px]">' + quoteMatch[1] + '</blockquote>';
                }
                const bulletMatch = line.match(/^[-*]\s+(.*)$/);
                if (bulletMatch) {
                    return '<div class="wa-bullet flex items-start gap-1.5 my-0.5"><span class="text-emerald-600 font-bold select-none">•</span><span>' + bulletMatch[1] + '</span></div>';
                }
                return line;
            });
            text = processedLines.join('\n');

            // 4. Formatting
            text = text.replace(/(?<=^|[\s\(\[\{.,!?;:])\*([^\*\n]+?)\*(?=$|[\s\)\]\}.,!?;:])/g, '<strong>$1</strong>');
            text = text.replace(/(?<=^|[\s\(\[\{.,!?;:])_([^_\n]+?)_(?=$|[\s\)\]\}.,!?;:])/g, '<em>$1</em>');
            text = text.replace(/(?<=^|[\s\(\[\{.,!?;:])~([^~\n]+?)~(?=$|[\s\)\]\}.,!?;:])/g, '<del class="opacity-75">$1</del>');

            // 5. Dynamic tokens
            text = text.replace(/\{([a-zA-Z0-9_\-]+)\}/g, '<span class="wa-token-pill inline-flex items-center px-1.5 py-0.2 rounded font-mono text-[11px] font-semibold bg-emerald-100 text-emerald-800 border-0">{$1}</span>');

            // Restore code
            inlineCodes.forEach((ic, index) => {
                text = text.replace('___INLINECODE_' + index + '___', ic);
            });
            codeBlocks.forEach((cb, index) => {
                text = text.replace('___CODEBLOCK_' + index + '___', cb);
            });

            text = text.replace(/\n/g, '<br>');
            return text;
        },

        /**
         * Converts WhatsApp markdown string into WYSIWYG HTML for the visual editor.
         */
        markdownToWysiwygHtml: function (rawText) {
            if (!rawText) return '';
            let html = this.escapeHtml(rawText);

            html = html.replace(/(?<=^|[\s\(\[\{.,!?;:])\*([^\*\n]+?)\*(?=$|[\s\)\]\}.,!?;:])/g, '<b>$1</b>');
            html = html.replace(/(?<=^|[\s\(\[\{.,!?;:])_([^_\n]+?)_(?=$|[\s\)\]\}.,!?;:])/g, '<i>$1</i>');
            html = html.replace(/(?<=^|[\s\(\[\{.,!?;:])~([^~\n]+?)~(?=$|[\s\)\]\}.,!?;:])/g, '<s>$1</s>');
            html = html.replace(/`([^`\n]+)`/g, '<code class="font-mono bg-slate-100 px-1 py-0.5 rounded text-[12px] text-slate-800">$1</code>');
            html = html.replace(/\{([a-zA-Z0-9_\-]+)\}/g, '<span class="wa-token-chip inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-mono font-medium text-emerald-800 bg-emerald-50 select-none border-0" contenteditable="false">{$1}</span>');
            html = html.replace(/\n/g, '<br>');
            return html;
        },

        /**
         * Serializes the ContentEditable DOM tree back into standard WhatsApp markdown for form submit.
         * @param {HTMLElement} element
         * @returns {string} WhatsApp markdown
         */
        serializeToWhatsApp: function (element) {
            if (!element) return '';
            if (typeof document === 'undefined') return element.textContent || '';

            const clone = element.cloneNode(true);

            clone.querySelectorAll('blockquote').forEach(bq => {
                bq.replaceWith('\n> ' + bq.textContent.trim() + '\n');
            });

            clone.querySelectorAll('li').forEach(li => {
                li.replaceWith('\n- ' + li.textContent.trim());
            });

            clone.querySelectorAll('pre, pre code').forEach(code => {
                code.replaceWith('```' + code.textContent + '```');
            });

            clone.querySelectorAll('code').forEach(code => {
                code.replaceWith('`' + code.textContent + '`');
            });

            clone.querySelectorAll('b, strong').forEach(b => {
                const text = b.textContent;
                if (text) b.replaceWith('*' + text + '*');
            });

            clone.querySelectorAll('i, em').forEach(i => {
                const text = i.textContent;
                if (text) i.replaceWith('_' + text + '_');
            });

            clone.querySelectorAll('s, strike, del').forEach(s => {
                const text = s.textContent;
                if (text) s.replaceWith('~' + text + '~');
            });

            clone.querySelectorAll('.wa-token-chip').forEach(chip => {
                chip.replaceWith(chip.textContent.trim());
            });

            let html = clone.innerHTML;
            html = html.replace(/<br\s*[\/]?>/gi, '\n');
            html = html.replace(/<\/div><div>/gi, '\n');
            html = html.replace(/<div>/gi, '\n');
            html = html.replace(/<\/div>/gi, '');
            html = html.replace(/<p>/gi, '\n');
            html = html.replace(/<\/p>/gi, '');

            const temp = document.createElement('div');
            temp.innerHTML = html;
            let text = temp.textContent || temp.innerText || '';

            return text.replace(/^\n+/, '').replace(/\n+$/, '');
        },

        /**
         * Renders the complete authentic WhatsApp chat bubble markup.
         * @param {string} text
         * @returns {string}
         */
        renderChatBubble: function (text) {
            const trimmed = (text || '').trim();
            if (!trimmed) {
                return '<div class="wa-preview-empty text-center py-6 text-slate-400 text-xs italic select-none flex flex-col items-center justify-center gap-1.5"><span class="text-slate-300 text-lg">💬</span><span>Start typing in Compose tab to preview live WhatsApp message...</span></div>';
            }

            const formatted = this.parse(trimmed);
            const now = new Date();
            let timeStr = '12:00 PM';
            try {
                timeStr = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            } catch (e) {
                timeStr = '12:00 PM';
            }

            return `
                <div class="flex justify-end w-full animate-fadeIn select-text">
                    <div class="relative max-w-[92%] sm:max-w-[82%] bg-[#d9fdd3] text-slate-900 shadow-sm border border-emerald-200/60 rounded-xl rounded-tr-none px-3 pt-2 pb-1.5 text-[13px] leading-relaxed break-words font-sans">
                        <div class="wa-bubble-content">${formatted}</div>
                        <div class="flex items-center justify-end gap-1 mt-1 text-[10px] text-slate-500/80 font-sans select-none float-right ml-3">
                            <span>${timeStr}</span>
                            ${ICONS.doubleCheck}
                        </div>
                    </div>
                </div>
            `;
        },

        /**
         * Injects scoped styles to completely strip borders and boxes from toolbar buttons and emoji cells,
         * completely immune to global app.css button styles!
         */
        injectMinimalistStyles: function () {
            if (typeof document === 'undefined') return;
            if (document.getElementById('wa-studio-minimalist-css')) return;

            const style = document.createElement('style');
            style.id = 'wa-studio-minimalist-css';
            style.textContent = `
                /* Absolute reset for WaStudio toolbar buttons and emoji cells (kills app.css boxes) */
                .wa-studio-container button,
                .wa-studio-toolbar button,
                .wa-emoji-popover button,
                .wa-emoji-cell {
                    min-height: unset !important;
                    padding: 0 !important;
                    margin: 0 !important;
                    border: none !important;
                    outline: none !important;
                    background: transparent !important;
                    box-shadow: none !important;
                    color: inherit !important;
                    font: inherit !important;
                    font-weight: normal !important;
                    transform: none !important;
                    display: inline-flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    cursor: pointer !important;
                }
                .wa-studio-container button:hover,
                .wa-emoji-popover button:hover {
                    transform: none !important;
                    box-shadow: none !important;
                }
                .wa-visual-editor:empty:before {
                    content: attr(data-placeholder);
                    color: #94a3b8;
                    pointer-events: none;
                    display: block;
                }
                .wa-visual-editor b, .wa-visual-editor strong {
                    font-weight: 700 !important;
                }
                .wa-visual-editor i, .wa-visual-editor em {
                    font-style: italic !important;
                }
                .wa-visual-editor s, .wa-visual-editor del, .wa-visual-editor strike {
                    text-decoration: line-through !important;
                }
                /* Custom slim scrollbar for emoji popover */
                .wa-emoji-scroll::-webkit-scrollbar {
                    width: 4px;
                }
                .wa-emoji-scroll::-webkit-scrollbar-track {
                    background: transparent;
                }
                .wa-emoji-scroll::-webkit-scrollbar-thumb {
                    background: #cbd5e1;
                    border-radius: 4px;
                }
                .wa-emoji-scroll::-webkit-scrollbar-thumb:hover {
                    background: #94a3b8;
                }
            `;
            document.head.appendChild(style);
        },

        /**
         * Upgrades a raw textarea with the Minimalist WYSIWYG Visual Editor,
         * Active Format State Highlighting, and Scrollable Non-Blocking Emoji Drawer.
         * @param {HTMLTextAreaElement} textarea
         */
        upgrade: function (textarea) {
            if (!textarea || textarea.dataset.waStudioUpgraded) return;
            textarea.dataset.waStudioUpgraded = 'true';

            this.injectMinimalistStyles();

            // 1. Create main studio container
            const wrapper = document.createElement('div');
            wrapper.className = 'wa-studio-container relative bg-white border border-slate-200 rounded-xl shadow-xs focus-within:border-slate-300 focus-within:ring-1 focus-within:ring-slate-200 transition-all flex flex-col group';

            if (textarea.classList.contains('mb-6')) {
                wrapper.classList.add('mb-6');
                textarea.classList.remove('mb-6');
            }

            textarea.parentNode.insertBefore(wrapper, textarea);
            textarea.style.display = 'none';

            // 2. Build Minimalist Toolbar Header
            const toolbar = document.createElement('div');
            toolbar.className = 'wa-studio-toolbar bg-slate-50/70 border-b border-slate-100/90 px-3 py-1.5 flex flex-wrap items-center justify-between gap-1 select-none relative';

            // Left actions: Minimalist format buttons
            const leftGroup = document.createElement('div');
            leftGroup.className = 'wa-format-group flex items-center gap-0.5 flex-wrap';

            const formatBtnElements = [];

            const formats = [
                { id: 'bold', icon: ICONS.bold, cmd: 'bold', title: 'Bold', shortcut: 'Ctrl+B' },
                { id: 'italic', icon: ICONS.italic, cmd: 'italic', title: 'Italic', shortcut: 'Ctrl+I' },
                { id: 'strike', icon: ICONS.strike, cmd: 'strikeThrough', title: 'Strikethrough', shortcut: 'Ctrl+Shift+X' },
                { id: 'code', icon: ICONS.code, cmd: 'code', title: 'Code block', shortcut: 'Ctrl+`' },
                { id: 'quote', icon: ICONS.quote, cmd: 'quote', title: 'Quote', shortcut: '> line' },
                { id: 'bullet', icon: ICONS.bullet, cmd: 'insertUnorderedList', title: 'Bullet list', shortcut: '- item' }
            ];

            formats.forEach(f => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'wa-tool-btn w-7 h-7 rounded text-slate-500 hover:text-slate-900 hover:bg-slate-200/60 active:scale-95 transition-all text-xs border-0 bg-transparent';
                btn.innerHTML = f.icon;
                btn.title = f.title + ' (' + f.shortcut + ')';
                btn.setAttribute('aria-label', f.title);
                btn.setAttribute('data-cmd', f.cmd);

                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (previewContainer && !previewContainer.classList.contains('hidden')) {
                        switchTab('write');
                    }
                    visualEditor.focus();

                    if (f.cmd === 'bold' || f.cmd === 'italic' || f.cmd === 'strikeThrough' || f.cmd === 'insertUnorderedList') {
                        document.execCommand(f.cmd, false, null);
                    } else if (f.cmd === 'code') {
                        const sel = window.getSelection();
                        if (sel && sel.rangeCount > 0) {
                            const range = sel.getRangeAt(0);
                            const selectedText = range.toString() || 'code';
                            const codeNode = document.createElement('code');
                            codeNode.className = 'font-mono bg-slate-100 px-1 py-0.5 rounded text-[12px] text-slate-800';
                            codeNode.textContent = selectedText;
                            range.deleteContents();
                            range.insertNode(codeNode);
                        }
                    } else if (f.cmd === 'quote') {
                        document.execCommand('formatBlock', false, 'blockquote');
                    }
                    syncFromVisualEditor();
                    updateActiveFormats();
                });

                formatBtnElements.push({ cmd: f.cmd, btn: btn });
                leftGroup.appendChild(btn);
            });

            // Minimal divider
            const divider1 = document.createElement('div');
            divider1.className = 'w-px h-3.5 bg-slate-200 mx-1';
            leftGroup.appendChild(divider1);

            // Borderless Minimal Emoji Trigger Button
            const emojiBtn = document.createElement('button');
            emojiBtn.type = 'button';
            emojiBtn.className = 'wa-emoji-trigger w-7 h-7 rounded text-slate-500 hover:text-amber-500 hover:bg-amber-50 active:scale-95 transition-all border-0 bg-transparent';
            emojiBtn.innerHTML = ICONS.smile;
            emojiBtn.title = 'Add emoji';
            emojiBtn.setAttribute('aria-label', 'Open emoji picker');
            leftGroup.appendChild(emojiBtn);

            // Parse presets
            const presets = textarea.dataset.presets ? JSON.parse(textarea.dataset.presets) : [];
            if (presets.length > 0) {
                const divider2 = document.createElement('div');
                divider2.className = 'w-px h-3.5 bg-slate-200 mx-1';
                leftGroup.appendChild(divider2);

                presets.forEach(p => {
                    const tagBtn = document.createElement('button');
                    tagBtn.type = 'button';
                    tagBtn.className = 'inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-mono font-medium text-emerald-700 bg-emerald-50 hover:bg-emerald-100/80 active:scale-95 transition-colors cursor-pointer border-0 shadow-none';
                    tagBtn.innerHTML = ICONS.tag + '<span>' + p + '</span>';
                    tagBtn.title = 'Insert ' + p;

                    tagBtn.addEventListener('click', (e) => {
                        e.preventDefault();
                        if (previewContainer && !previewContainer.classList.contains('hidden')) {
                            switchTab('write');
                        }
                        visualEditor.focus();
                        const sel = window.getSelection();
                        if (sel && sel.rangeCount > 0) {
                            const range = sel.getRangeAt(0);
                            const chip = document.createElement('span');
                            chip.className = 'wa-token-chip inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-mono font-medium text-emerald-800 bg-emerald-50 select-none border-0';
                            chip.setAttribute('contenteditable', 'false');
                            chip.textContent = p;

                            range.deleteContents();
                            range.insertNode(chip);

                            range.setStartAfter(chip);
                            range.setEndAfter(chip);
                            sel.removeAllRanges();
                            sel.addRange(range);
                        }
                        syncFromVisualEditor();
                    });

                    leftGroup.appendChild(tagBtn);
                });
            }

            toolbar.appendChild(leftGroup);

            // Right group: Minimal Tab Switcher
            const rightGroup = document.createElement('div');
            rightGroup.className = 'flex items-center gap-1 select-none ml-auto';

            const tabSwitcher = document.createElement('div');
            tabSwitcher.className = 'inline-flex items-center p-0.5 bg-slate-100 rounded-lg text-[11px] font-medium border-0';

            const writeTabBtn = document.createElement('button');
            writeTabBtn.type = 'button';
            writeTabBtn.className = 'wa-tab-write px-2.5 py-0.5 rounded-md text-slate-800 bg-white shadow-xs font-semibold flex items-center gap-1 transition-all border-0';
            writeTabBtn.innerHTML = ICONS.edit + '<span>Write</span>';

            const previewTabBtn = document.createElement('button');
            previewTabBtn.type = 'button';
            previewTabBtn.className = 'wa-tab-preview px-2.5 py-0.5 rounded-md text-slate-500 hover:text-slate-800 flex items-center gap-1 transition-all border-0';
            previewTabBtn.innerHTML = ICONS.preview + '<span>WA Preview</span>';

            tabSwitcher.appendChild(writeTabBtn);
            tabSwitcher.appendChild(previewTabBtn);
            rightGroup.appendChild(tabSwitcher);
            toolbar.appendChild(rightGroup);

            wrapper.appendChild(toolbar);

            // 3. Scrollable & Non-Blocking Emoji Popover Drawer
            // Positioned smoothly below toolbar or floating cleanly to the side so message text remains 100% visible
            const emojiPopover = document.createElement('div');
            emojiPopover.className = 'wa-emoji-popover hidden absolute left-2 top-full mt-1.5 bg-white border border-slate-200/90 rounded-xl shadow-xl p-3 z-40 w-72 max-w-[95vw] transition-all select-none animate-fadeIn';
            
            const popoverHeader = document.createElement('div');
            popoverHeader.className = 'flex items-center justify-between pb-1.5 mb-2 border-b border-slate-100 text-[11px] font-semibold text-slate-700';
            popoverHeader.innerHTML = '<span>Emojis</span>';
            const closeEmojiBtn = document.createElement('button');
            closeEmojiBtn.type = 'button';
            closeEmojiBtn.className = 'text-slate-400 hover:text-slate-700 p-0.5 rounded hover:bg-slate-100 border-0';
            closeEmojiBtn.innerHTML = ICONS.close;
            popoverHeader.appendChild(closeEmojiBtn);
            emojiPopover.appendChild(popoverHeader);

            // Scrollable Container for all emoji categories
            const emojiScrollContainer = document.createElement('div');
            emojiScrollContainer.className = 'wa-emoji-scroll max-h-56 overflow-y-auto pr-1 space-y-2';

            EMOJI_CATEGORIES.forEach(cat => {
                const catGroup = document.createElement('div');

                const catLabel = document.createElement('div');
                catLabel.className = 'text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1 px-0.5';
                catLabel.textContent = cat.name;
                catGroup.appendChild(catLabel);

                const grid = document.createElement('div');
                grid.className = 'grid grid-cols-8 gap-0.5';

                cat.emojis.forEach(emo => {
                    const emoCell = document.createElement('button');
                    emoCell.type = 'button';
                    emoCell.className = 'wa-emoji-cell w-7 h-7 flex items-center justify-center text-base rounded hover:bg-slate-100/80 active:scale-95 transition-all cursor-pointer select-none border-0 bg-transparent';
                    emoCell.textContent = emo;
                    emoCell.title = emo;

                    emoCell.addEventListener('click', (e) => {
                        e.preventDefault();
                        if (previewContainer && !previewContainer.classList.contains('hidden')) {
                            switchTab('write');
                        }
                        visualEditor.focus();
                        document.execCommand('insertText', false, emo);
                        syncFromVisualEditor();
                    });

                    grid.appendChild(emoCell);
                });

                catGroup.appendChild(grid);
                emojiScrollContainer.appendChild(catGroup);
            });

            emojiPopover.appendChild(emojiScrollContainer);
            wrapper.appendChild(emojiPopover);

            // Smart popover position toggle: if toolbar is close to bottom, show above; else show below
            const toggleEmoji = (open) => {
                const shouldOpen = open !== undefined ? open : emojiPopover.classList.contains('hidden');
                if (shouldOpen) {
                    const rect = wrapper.getBoundingClientRect();
                    // If bottom space is constrained, float above
                    if (window.innerHeight - rect.bottom < 260 && rect.top > 260) {
                        emojiPopover.style.top = 'auto';
                        emojiPopover.style.bottom = '100%';
                        emojiPopover.style.marginBottom = '6px';
                        emojiPopover.style.marginTop = '0';
                    } else {
                        emojiPopover.style.top = '100%';
                        emojiPopover.style.bottom = 'auto';
                        emojiPopover.style.marginTop = '6px';
                        emojiPopover.style.marginBottom = '0';
                    }
                    emojiPopover.classList.remove('hidden');
                    emojiBtn.classList.add('bg-amber-50', 'text-amber-500');
                } else {
                    emojiPopover.classList.add('hidden');
                    emojiBtn.classList.remove('bg-amber-50', 'text-amber-500');
                }
            };

            emojiBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                toggleEmoji();
            });

            closeEmojiBtn.addEventListener('click', (e) => {
                e.preventDefault();
                toggleEmoji(false);
            });

            document.addEventListener('click', (e) => {
                if (!wrapper.contains(e.target)) {
                    toggleEmoji(false);
                }
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !emojiPopover.classList.contains('hidden')) {
                    toggleEmoji(false);
                }
            });

            // 4. WYSIWYG Visual Editor Pane
            const composePane = document.createElement('div');
            composePane.className = 'wa-compose-pane block';

            const visualEditor = document.createElement('div');
            visualEditor.className = 'wa-visual-editor w-full p-3.5 text-[13px] text-slate-800 min-h-[110px] max-h-[360px] overflow-y-auto focus:outline-none font-sans leading-relaxed select-text';
            visualEditor.setAttribute('contenteditable', 'true');
            visualEditor.setAttribute('spellcheck', 'true');
            visualEditor.setAttribute('data-placeholder', textarea.placeholder || 'Type your WhatsApp message here...');

            if (textarea.value) {
                visualEditor.innerHTML = this.markdownToWysiwygHtml(textarea.value);
            }

            composePane.appendChild(visualEditor);
            wrapper.appendChild(composePane);

            // WhatsApp Live Preview Pane
            const previewContainer = document.createElement('div');
            previewContainer.className = 'wa-preview-pane hidden bg-[#efeae2]/40 p-4 min-h-[120px] max-h-[360px] overflow-y-auto transition-all flex flex-col justify-end';
            wrapper.appendChild(previewContainer);

            // Tab Switching Logic
            const switchTab = (mode) => {
                toggleEmoji(false);
                if (mode === 'preview') {
                    composePane.classList.add('hidden');
                    previewContainer.classList.remove('hidden');
                    writeTabBtn.className = 'wa-tab-write px-2.5 py-0.5 rounded-md text-slate-500 hover:text-slate-800 transition-all flex items-center gap-1 border-0';
                    previewTabBtn.className = 'wa-tab-preview px-2.5 py-0.5 rounded-md text-slate-800 bg-white shadow-xs font-semibold flex items-center gap-1 transition-all border-0';
                    leftGroup.classList.add('opacity-40', 'pointer-events-none');
                    updatePreview();
                } else {
                    composePane.classList.remove('hidden');
                    previewContainer.classList.add('hidden');
                    writeTabBtn.className = 'wa-tab-write px-2.5 py-0.5 rounded-md text-slate-800 bg-white shadow-xs font-semibold flex items-center gap-1 transition-all border-0';
                    previewTabBtn.className = 'wa-tab-preview px-2.5 py-0.5 rounded-md text-slate-500 hover:text-slate-800 transition-all flex items-center gap-1 border-0';
                    leftGroup.classList.remove('opacity-40', 'pointer-events-none');
                    visualEditor.focus();
                }
            };

            writeTabBtn.addEventListener('click', () => switchTab('write'));
            previewTabBtn.addEventListener('click', () => switchTab('preview'));

            // 5. Active Formatting Highlighter (Real-time detection on selection/cursor)
            const updateActiveFormats = () => {
                if (typeof document === 'undefined') return;
                const sel = window.getSelection();
                if (!sel || sel.rangeCount === 0) return;

                const anchorNode = sel.anchorNode;
                if (!anchorNode || !visualEditor.contains(anchorNode)) {
                    formatBtnElements.forEach(item => {
                        item.btn.classList.remove('bg-emerald-100', 'text-emerald-800', 'font-bold');
                        item.btn.classList.add('text-slate-500');
                    });
                    return;
                }

                let isBold = false, isItalic = false, isStrike = false, isList = false;
                try { isBold = document.queryCommandState('bold'); } catch (e) {}
                try { isItalic = document.queryCommandState('italic'); } catch (e) {}
                try { isStrike = document.queryCommandState('strikeThrough'); } catch (e) {}
                try { isList = document.queryCommandState('insertUnorderedList'); } catch (e) {}

                let isCode = false, isQuote = false;
                let curr = anchorNode;
                while (curr && curr !== visualEditor) {
                    if (curr.nodeName === 'CODE' || curr.nodeName === 'PRE') isCode = true;
                    if (curr.nodeName === 'BLOCKQUOTE') isQuote = true;
                    curr = curr.parentNode;
                }

                formatBtnElements.forEach(item => {
                    let active = false;
                    if (item.cmd === 'bold') active = isBold;
                    else if (item.cmd === 'italic') active = isItalic;
                    else if (item.cmd === 'strikeThrough') active = isStrike;
                    else if (item.cmd === 'insertUnorderedList') active = isList;
                    else if (item.cmd === 'code') active = isCode;
                    else if (item.cmd === 'quote') active = isQuote;

                    if (active) {
                        item.btn.classList.add('bg-emerald-100', 'text-emerald-800', 'font-bold');
                        item.btn.classList.remove('text-slate-500');
                    } else {
                        item.btn.classList.remove('bg-emerald-100', 'text-emerald-800', 'font-bold');
                        item.btn.classList.add('text-slate-500');
                    }
                });
            };

            document.addEventListener('selectionchange', updateActiveFormats);
            visualEditor.addEventListener('keyup', updateActiveFormats);
            visualEditor.addEventListener('mouseup', updateActiveFormats);
            visualEditor.addEventListener('touchend', updateActiveFormats);

            // 6. Minimal Footer Status Bar
            const footer = document.createElement('div');
            footer.className = 'wa-studio-footer bg-slate-50/30 border-t border-slate-100/70 px-3 py-1 flex items-center justify-between text-[11px] text-slate-400 font-mono select-none';
            
            const countSpan = document.createElement('span');
            countSpan.className = 'wa-char-count font-medium';
            countSpan.textContent = '0 chars';
            footer.appendChild(countSpan);

            const segmentSpan = document.createElement('span');
            segmentSpan.className = 'wa-segment-info text-[10px] text-slate-400';
            segmentSpan.textContent = '1 message segment';
            footer.appendChild(segmentSpan);

            wrapper.appendChild(footer);

            // 7. Two-Way Sync between Visual Editor and Underlying Textarea
            const updatePreview = () => {
                if (!previewContainer.classList.contains('hidden')) {
                    previewContainer.innerHTML = this.renderChatBubble(textarea.value);
                }
            };

            const syncFromVisualEditor = () => {
                const markdown = this.serializeToWhatsApp(visualEditor);
                textarea.value = markdown;

                const len = markdown.length;
                countSpan.textContent = len + ' chars';
                if (len > 4096) {
                    countSpan.classList.add('text-rose-500', 'font-bold');
                    segmentSpan.textContent = 'Exceeds 4,096 char limit';
                    segmentSpan.classList.add('text-rose-500');
                } else {
                    countSpan.classList.remove('text-rose-500', 'font-bold');
                    segmentSpan.classList.remove('text-rose-500');
                    const segments = Math.ceil(len / 1600) || 1;
                    segmentSpan.textContent = segments + (segments === 1 ? ' message segment' : ' message segments');
                }

                updatePreview();

                textarea.dispatchEvent(new Event('input', { bubbles: true }));
                textarea.dispatchEvent(new Event('change', { bubbles: true }));
            };

            visualEditor.addEventListener('input', syncFromVisualEditor);
            visualEditor.addEventListener('keyup', syncFromVisualEditor);
            visualEditor.addEventListener('paste', () => {
                setTimeout(syncFromVisualEditor, 10);
            });

            syncFromVisualEditor();

            const form = textarea.closest('form');
            if (form) {
                form.addEventListener('reset', () => {
                    setTimeout(() => {
                        visualEditor.innerHTML = '';
                        syncFromVisualEditor();
                    }, 10);
                });
            }
        },

        /**
         * Initializes all textareas on the page marked with .wa-editor
         */
        initAll: function () {
            if (typeof document === 'undefined') return;
            const editors = document.querySelectorAll('textarea.wa-editor');
            editors.forEach(el => this.upgrade(el));
        },

        /**
         * Upgrades a single textarea into the WaStudio engine
         * @param {HTMLTextAreaElement} textarea
         */
        init: function (textarea) {
            this.upgrade(textarea);
        }
    };

    return WaStudio;
}));
