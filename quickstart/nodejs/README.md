# ⚡ Node.js Quickstart — WhatsGateway

A production-ready Node.js client built with **zero external npm dependencies** (uses Node 18+ native `fetch`).

## 🚀 Quick Usage

### 1. Interactive Mode
Run directly in your terminal:
```bash
node send.js
```

### 2. Command Line Arguments
```bash
# Standard clean format (Recommended)
node send.js --key wa_live_YOUR_KEY --to 919876543210 --message "Hello from Node.js!"

# Meta WhatsApp Cloud API format (Drop-In)
node send.js --key wa_live_YOUR_KEY --to 919876543210 --message "Hello!" --format meta
```

### 3. As an Imported Module in Your Application
```javascript
const { WhatsAppClient } = require('./send');

const client = new WhatsAppClient(
  'wa_live_YOUR_KEY_HERE',
  'https://whatsapp-api-xepo.onrender.com/api/send.php' // Or local endpoint
);

async function notifyCustomer() {
  const result = await client.sendStandard('919876543210', 'Your order #9876 has been dispatched! 📦', {
    humanize: true
  });

  if (result.ok) {
    console.log('Queued message ID:', result.data.message_id);
  } else {
    console.error('Error:', result.data.error);
  }
}

notifyCustomer();
```

## ⚙️ Environment Variables
```bash
export WA_API_KEY="wa_live_YOUR_KEY"
export WA_API_URL="https://whatsapp-api-xepo.onrender.com/api/send.php"
```
