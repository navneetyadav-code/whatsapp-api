# 🚀 WhatsGateway — Developer Quickstart Directory

Complete, production-ready, zero-dependency source code programs in **5 major programming languages** to connect to your WhatsApp API Gateway in seconds.

---

## 📂 Quickstart Languages Directory

| Language | Directory | Key Feature | Run Command |
| :--- | :--- | :--- | :--- |
| **🐍 Python** | [`quickstart/python/`](python/) | Zero deps (uses `requests` or native `urllib`), CLI flags + interactive mode | `python send.py` |
| **⚡ Node.js** | [`quickstart/nodejs/`](nodejs/) | Native `fetch` (Node 18+), zero npm installs, OOP `WhatsAppClient` | `node send.js` |
| **🐘 PHP** | [`quickstart/php/`](php/) | Strict types, native `cURL`, OOP class for Laravel/Symfony | `php send.php` |
| **🐹 Go** | [`quickstart/go/`](go/) | Standard library `net/http`, compilable binary executable | `go run main.go` |
| **☕ Java** | [`quickstart/java/`](java/) | Java 11+ `java.net.http`, single-file execution, enterprise-ready | `java SendWhatsApp.java` |

---

## ⚡ Terminal / cURL Quickstart

If you just want to test sending a message immediately from your command line:

### 1. Standard Clean Format (Recommended)
```bash
curl -X POST "https://whatsapp-api-xepo.onrender.com/api/send.php" \
  -H "Content-Type: application/json" \
  -H "X-API-Key: wa_live_YOUR_API_KEY" \
  -d '{
    "to": "919876543210",
    "message": "Hello from Terminal! 🚀",
    "humanize": true
  }'
```

### 2. Meta WhatsApp Cloud API Format (Drop-In)
```bash
curl -X POST "https://whatsapp-api-xepo.onrender.com/api/send.php" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer wa_live_YOUR_API_KEY" \
  -d '{
    "messaging_product": "whatsapp",
    "recipient_type": "individual",
    "to": "919876543210",
    "type": "text",
    "text": {
      "body": "Hello via Meta Cloud API schema! 🌐"
    }
  }'
```

---

## 🔑 Authentication
All quickstart clients accept your API Key via:
1. **Interactive Prompt:** Run without flags and enter when prompted.
2. **CLI Flags:** e.g. `--key wa_live_...` or `-key wa_live_...`.
3. **Environment Variable:** Set `WA_API_KEY=wa_live_...` and `WA_API_URL=https://...`.

---

## 🛡️ Anti-Ban Best Practices
- Keep `"humanize": true` enabled to simulate realistic typing speed and human keystroke intervals.
- Only send to users who have opted into receiving notifications.
