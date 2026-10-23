#!/usr/bin/env node
/**
 * WhatsGateway — Node.js Quickstart
 * Production-ready JavaScript/Node.js client supporting both Standard and Meta formats.
 *
 * Requirements: Node.js 18+ (Uses native fetch, 0 external dependencies)
 *
 * Usage:
 *   node send.js
 *   node send.js --to 919876543210 --message "Hello from Node.js!"
 *   node send.js --to 919876543210 --message "Hello!" --format meta
 */

const readline = require('readline');

class WhatsAppClient {
  static DEFAULT_URL = 'https://whatsapp-api-xepo.onrender.com/api/send.php';

  constructor(apiKey, apiUrl = WhatsAppClient.DEFAULT_URL) {
    this.apiKey = apiKey || process.env.WA_API_KEY || '';
    this.apiUrl = (apiUrl || process.env.WA_API_URL || WhatsAppClient.DEFAULT_URL).replace(/\/$/, '');

    if (!this.apiKey) {
      throw new Error('API Key is required. Set WA_API_KEY environment variable or pass to constructor.');
    }
  }

  /**
   * Send message using the Standard Clean Flat JSON format
   */
  async sendStandard(to, message, options = {}) {
    const payload = {
      to: String(to).trim(),
      message: String(message).trim(),
      humanize: options.humanize !== false,
      ...(options.campaignId ? { campaign_id: options.campaignId } : {})
    };

    const headers = {
      'Content-Type': 'application/json',
      'X-API-Key': this.apiKey
    };

    return this.#post(payload, headers);
  }

  /**
   * Send message using the Meta WhatsApp Cloud API format (Drop-In)
   */
  async sendMetaFormat(to, body, options = {}) {
    const payload = {
      messaging_product: 'whatsapp',
      recipient_type: 'individual',
      to: String(to).trim(),
      type: 'text',
      text: {
        preview_url: Boolean(options.previewUrl),
        body: String(body).trim()
      }
    };

    const headers = {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${this.apiKey}`
    };

    return this.#post(payload, headers);
  }

  async #post(payload, headers) {
    try {
      const response = await fetch(this.apiUrl, {
        method: 'POST',
        headers,
        body: JSON.stringify(payload)
      });

      const data = await response.json().catch(() => ({}));
      const ok = response.ok && data.ok !== false;

      return {
        ok,
        status: response.status,
        data
      };
    } catch (error) {
      return {
        ok: false,
        status: 0,
        error: error.message
      };
    }
  }
}

// Interactive helper
function ask(query) {
  const rl = readline.createInterface({
    input: process.stdin,
    output: process.stdout
  });
  return new Promise((resolve) => rl.question(query, (ans) => {
    rl.close();
    resolve(ans.trim());
  }));
}

// CLI argument parsing
function parseArgs() {
  const args = process.argv.slice(2);
  const parsed = {};
  for (let i = 0; i < args.length; i++) {
    const arg = args[i];
    if (arg.startsWith('--')) {
      const key = arg.replace(/^--/, '');
      const next = args[i + 1];
      if (next && !next.startsWith('--')) {
        parsed[key] = next;
        i++;
      } else {
        parsed[key] = true;
      }
    }
  }
  return parsed;
}

async function main() {
  const args = parseArgs();

  if (args.help || args.h) {
    console.log(`WhatsGateway — Node.js Quickstart
Usage:
  node send.js [options]

Options:
  --to <phone>       Recipient phone number (e.g. 919876543210)
  --message <text>   Message content
  --key <api_key>    API Key (wa_live_...)
  --url <api_url>    API Gateway endpoint
  --format <type>    Format: 'standard' (default) or 'meta'
  --no-humanize      Disable humanized typing delays
  --help, -h         Show this help message
`);
    process.exit(0);
  }

  let apiKey = args.key || process.env.WA_API_KEY;
  if (!apiKey) {
    apiKey = await ask('Enter your API Key (wa_live_...): ');
    if (!apiKey) {
      console.error('[!] API Key is required.');
      process.exit(1);
    }
  }

  const apiUrl = args.url || process.env.WA_API_URL || WhatsAppClient.DEFAULT_URL;
  const to = args.to || (await ask('Enter recipient phone number (with country code): '));
  const message = args.message || (await ask('Enter message text: '));
  const format = args.format || 'standard';

  console.log(`\n[*] Sending message via [${format.toUpperCase()}] format...`);
  console.log(`[*] Endpoint:  ${apiUrl}`);
  console.log(`[*] Recipient: ${to}`);

  const client = new WhatsAppClient(apiKey, apiUrl);
  let result;

  if (format === 'meta') {
    result = await client.sendMetaFormat(to, message);
  } else {
    result = await client.sendStandard(to, message, {
      humanize: args['no-humanize'] !== true
    });
  }

  if (result.ok) {
    console.log('\n[+] SUCCESS! Message queued for delivery:');
    console.log(JSON.stringify(result.data, null, 2));
  } else {
    console.error('\n[-] FAILED to send message:');
    console.error(JSON.stringify(result.data || { error: result.error }, null, 2));
    process.exit(1);
  }
}

if (require.main === module) {
  main().catch((err) => {
    console.error('[!] Uncaught error:', err.message);
    process.exit(1);
  });
}

module.exports = { WhatsAppClient };
