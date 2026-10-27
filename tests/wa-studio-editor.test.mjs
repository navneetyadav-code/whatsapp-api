import fs from 'node:fs';
import test from 'node:test';
import assert from 'node:assert/strict';
import WaStudio from '../public/assets/wa-studio.js';

test('WaStudio Phase 1: Core Engine and Security Hardening', async (t) => {
    
    await t.test('XSS Sanitization: Raw HTML tags and script injections are strictly escaped', () => {
        const malicious = '<script>alert("hacked")</script><img src="x" onerror="steal()"/>';
        const parsed = WaStudio.parse(malicious);
        
        assert.ok(!parsed.includes('<script>'), 'Must not contain raw <script> tag');
        assert.ok(!parsed.includes('<img'), 'Must not contain raw <img> tag');
        assert.ok(parsed.includes('&lt;script&gt;'), 'Must escape <script>');
        assert.ok(parsed.includes('&lt;img'), 'Must escape <img>');
    });

    await t.test('WhatsApp Bold: *text* converts to <strong>', () => {
        const input = 'Hello *world* test';
        const parsed = WaStudio.parse(input);
        assert.ok(parsed.includes('<strong>world</strong>'), 'Bold markup must be converted to strong');
        assert.ok(!parsed.includes('*world*'), 'Raw asterisks must be removed');
    });

    await t.test('WhatsApp Italic: _text_ converts to <em>', () => {
        const input = 'Hello _italic text_ test';
        const parsed = WaStudio.parse(input);
        assert.ok(parsed.includes('<em>italic text</em>'), 'Italic markup must be converted to em');
    });

    await t.test('WhatsApp Strikethrough: ~text~ converts to <del>', () => {
        const input = 'Discount: ~100~ 50';
        const parsed = WaStudio.parse(input);
        assert.ok(parsed.includes('<del class="opacity-75">100</del>'), 'Strikethrough markup must be converted to del');
    });

    await t.test('WhatsApp Code Blocks and Inline Code: ```code``` and `code`', () => {
        const codeBlockInput = 'Here is code:\n```const x = *not_bold*;```';
        const parsedBlock = WaStudio.parse(codeBlockInput);
        assert.ok(parsedBlock.includes('<pre class="wa-code-block'), 'Must create code block');
        assert.ok(parsedBlock.includes('const x = *not_bold*;'), 'Must not format markdown inside code block');

        const inlineInput = 'Run `npm test` command';
        const parsedInline = WaStudio.parse(inlineInput);
        assert.ok(parsedInline.includes('<code class="wa-inline-code'), 'Must create inline code');
        assert.ok(parsedInline.includes('npm test'), 'Must contain inline code text');
    });

    await t.test('WhatsApp Quotes: > quote converts to <blockquote>', () => {
        const input = '> This is a quoted message';
        const parsed = WaStudio.parse(input);
        assert.ok(parsed.includes('<blockquote class="wa-quote'), 'Must format blockquote');
        assert.ok(parsed.includes('This is a quoted message'), 'Must contain quoted text');
    });

    await t.test('WhatsApp Bullet Lists: - item converts to bullet element', () => {
        const input = '- Item 1\n- Item 2';
        const parsed = WaStudio.parse(input);
        assert.ok(parsed.includes('wa-bullet'), 'Must format bullet element');
        assert.ok(parsed.includes('Item 1'), 'Must contain Item 1');
        assert.ok(parsed.includes('Item 2'), 'Must contain Item 2');
    });

    await t.test('Dynamic Tokens: {name} and {phone} convert to styled token badges', () => {
        const input = 'Hello {name}, your phone is {phone}';
        const parsed = WaStudio.parse(input);
        assert.ok(parsed.includes('<span class="wa-token-pill'), 'Must style dynamic token');
        assert.ok(parsed.includes('{name}'), 'Must preserve token text {name}');
        assert.ok(parsed.includes('{phone}'), 'Must preserve token text {phone}');
    });

    await t.test('Multiline Preservation: Newlines convert to <br>', () => {
        const input = 'Line 1\nLine 2\n\nLine 3';
        const parsed = WaStudio.parse(input);
        assert.ok(parsed.includes('<br>'), 'Newlines must convert to <br>');
    });

    await t.test('Edge Cases: Empty string, non-string, and ReDoS protection', () => {
        assert.equal(WaStudio.parse(''), '');
        assert.equal(WaStudio.parse(null), '');
        assert.equal(WaStudio.parse(undefined), '');

        const massive = 'A'.repeat(30000);
        const parsedMassive = WaStudio.parse(massive);
        assert.ok(parsedMassive.length > 0, 'Massive input must parse without crashing');
        assert.ok(parsedMassive.includes('[truncated]'), 'Massive payload must be safely bounded');
    });
});

test('WaStudio WYSIWYG Engine: Zero Asterisks in Visual Editor and Bidirectional Serialization', async (t) => {

    await t.test('markdownToWysiwygHtml: Strips asterisks and renders true visual bold tags', () => {
        const input = 'Welcome *Navneet*, order _#100_ is ~pending~!';
        const html = WaStudio.markdownToWysiwygHtml(input);

        assert.ok(html.includes('<b>Navneet</b>'), 'Bold must render as visual <b> without asterisks');
        assert.ok(!html.includes('*Navneet*'), 'Must NOT contain asterisks around bold text');
        assert.ok(html.includes('<i>#100</i>'), 'Italic must render as visual <i> without underscores');
        assert.ok(html.includes('<s>pending</s>'), 'Strikethrough must render as visual <s> without tildes');
    });

    await t.test('serializeToWhatsApp: Formats visual DOM nodes back into WhatsApp markdown for form submit', () => {
        // Mock a visual DOM node
        const mockDiv = {
            cloneNode: function() {
                return {
                    querySelectorAll: () => [],
                    innerHTML: '<b>Hello</b> <i>World</i>'
                };
            },
            textContent: '*Hello* _World_'
        };

        const serialized = WaStudio.serializeToWhatsApp(mockDiv);
        assert.ok(serialized.includes('*Hello*'), 'Serialized payload must format bold with single asterisks for WhatsApp');
        assert.ok(serialized.includes('_World_'), 'Serialized payload must format italic with single underscores for WhatsApp');
    });
});

test('WaStudio Minimalist Design & Minimal Boxless Toolbar', async (t) => {
    
    await t.test('Authored SVG Icon Assets: All icons exist with uniform geometry', () => {
        assert.ok(WaStudio.ICONS, 'WaStudio.ICONS must be defined');
        const requiredIcons = ['bold', 'italic', 'strike', 'code', 'quote', 'bullet', 'tag', 'edit', 'preview', 'doubleCheck', 'smile', 'close'];
        
        requiredIcons.forEach(iconName => {
            const svg = WaStudio.ICONS[iconName];
            assert.ok(svg, `Icon ${iconName} must exist`);
            assert.ok(svg.includes('<svg'), `Icon ${iconName} must be valid SVG`);
        });
    });

    await t.test('EMOJI_CATEGORIES: Defined with WhatsApp high-converting marketing sets', () => {
        assert.ok(Array.isArray(WaStudio.EMOJI_CATEGORIES), 'EMOJI_CATEGORIES must be an array');
        assert.ok(WaStudio.EMOJI_CATEGORIES.length >= 3, 'Must have at least 3 categories');

        const catNames = WaStudio.EMOJI_CATEGORIES.map(c => c.name);
        assert.ok(catNames.includes('Reactions'), 'Must have Reactions category');
        assert.ok(catNames.includes('Business & Sales'), 'Must have Business & Sales category');
        assert.ok(catNames.includes('Support & Alerts'), 'Must have Support & Alerts category');
    });
});

test('WaStudio Phase 3: Live WhatsApp Chat Bubble Simulator', async (t) => {

    await t.test('renderChatBubble: Empty message produces elegant empty state', () => {
        const emptyResult = WaStudio.renderChatBubble('');
        assert.ok(emptyResult.includes('wa-preview-empty'), 'Empty result must render empty state container');
        assert.ok(emptyResult.includes('Start typing in Compose tab'), 'Empty state must have clear prompt');
    });

    await t.test('renderChatBubble: Formats message in authentic green bubble with timestamp & checkmarks', () => {
        const msg = 'Order *#4092* for {name} is confirmed! 🚀';
        const result = WaStudio.renderChatBubble(msg);

        assert.ok(result.includes('bg-[#d9fdd3]'), 'Must render WhatsApp outgoing green bubble background');
        assert.ok(result.includes('<strong>#4092</strong>'), 'Must format bold tags inside chat bubble');
        assert.ok(result.includes('{name}'), 'Must format dynamic tokens');
        assert.ok(result.includes('wa-bubble-content'), 'Must render bubble content container');
        assert.ok(result.includes('text-[#53bdeb]'), 'Must include WhatsApp blue double checkmark receipt');
    });

    await t.test('renderChatBubble: Strictly sanitizes XSS in chat bubble preview', () => {
        const malicious = '<script>alert("xss")</script>*safe text*';
        const result = WaStudio.renderChatBubble(malicious);

        assert.ok(!result.includes('<script>'), 'Must not execute or render raw script');
        assert.ok(result.includes('&lt;script&gt;'), 'Must entity escape script tags');
        assert.ok(result.includes('<strong>safe text</strong>'), 'Must parse safe markdown');
    });
});


test('WaStudio Typography & Active Format Highlight Inspection', async (t) => {

    const source = fs.readFileSync('public/assets/wa-studio.js', 'utf8');

    await t.test('Message Content Font Size: Configured for large 15px/16px readability', () => {
        assert.ok(source.includes('text-[15px] sm:text-[16px]'), 'Visual editor and bubble must have 15px/16px class');
        assert.ok(source.includes('font-size: 15px !important;'), 'CSS must specify 15px base size');
        assert.ok(source.includes('font-size: 16px !important;'), 'CSS must specify 16px responsive desktop size');
    });

    await t.test('Bold Typography: Configured with intense weight 800 and text shadow', () => {
        assert.ok(source.includes('font-weight: 800 !important;'), 'Bold elements must have 800 weight');
        assert.ok(source.includes('text-shadow: 0.2px 0 0 #020617 !important;'), 'Bold elements must reinforce weight via text-shadow');
    });

    await t.test('Active Formatter Highlighting: Toolbar button receives distinct border AND background', () => {
        assert.ok(source.includes('.wa-studio-toolbar button.wa-format-btn.is-active'), 'Must define .is-active class rule');
        assert.ok(source.includes('background: #dcfce7 !important;'), 'Active button must have rich emerald background');
        assert.ok(source.includes('border: 1.5px solid #059669 !important;'), 'Active button must have prominent emerald border');
        assert.ok(source.includes("item.btn.classList.add('is-active', 'active');"), 'Active state must apply is-active class to button');
    });

    await t.test('Event Listeners: updateActiveFormats bound to input, focus, selectionchange, keyup', () => {
        assert.ok(source.includes("visualEditor.addEventListener('input', updateActiveFormats);"), 'Must listen to input events');
        assert.ok(source.includes("visualEditor.addEventListener('focus', updateActiveFormats);"), 'Must listen to focus events');
        assert.ok(source.includes("document.addEventListener('selectionchange', updateActiveFormats);"), 'Must listen to selectionchange');
    });
});
