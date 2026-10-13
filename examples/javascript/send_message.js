/**
 * Free WhatsApp API - Node.js / JavaScript Example
 * Send WhatsApp messages using modern fetch in Node.js (v18+) or browser.
 */

const API_URL = "http://localhost/whatsapp-api/public/api/send.php";
const API_KEY = "wa_live_YOUR_API_KEY_HERE";
const RECIPIENT_PHONE = "919876543210";

async function sendWhatsAppMessage() {
  try {
    const response = await fetch(API_URL, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-API-Key": API_KEY,
      },
      body: JSON.stringify({
        to: RECIPIENT_PHONE,
        message: "Hello from Node.js / JavaScript! ⚡ Free WhatsApp API Gateway.",
        humanize: true,
      }),
    });

    const result = await response.json();
    console.log("Response:", result);
  } catch (error) {
    console.error("Failed to send WhatsApp message:", error);
  }
}

sendWhatsAppMessage();
