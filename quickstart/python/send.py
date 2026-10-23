#!/usr/bin/env python3
"""
WhatsGateway — Python Quickstart
Production-ready client supporting both Standard and Meta WhatsApp Cloud API formats.

Usage:
    # Interactive mode:
    python send.py

    # CLI mode:
    python send.py --to 919876543210 --message "Hello from Python!"
    python send.py --to 919876543210 --message "Hello!" --format meta
"""

import os
import sys
import json
import argparse
from typing import Optional, Dict, Any, Tuple

# Use requests if available, fallback to urllib (zero dependencies required)
try:
    import requests
    HAS_REQUESTS = True
except ImportError:
    import urllib.request
    import urllib.error
    HAS_REQUESTS = False


class WhatsAppHubClient:
    """Professional client for WhatsApp API Gateway."""

    DEFAULT_API_URL = "https://whatsapp-api-xepo.onrender.com/api/send.php"

    def __init__(self, api_key: Optional[str] = None, api_url: Optional[str] = None):
        self.api_key = api_key or os.getenv("WA_API_KEY", "")
        self.api_url = (api_url or os.getenv("WA_API_URL", self.DEFAULT_API_URL)).rstrip("/")

        if not self.api_key:
            raise ValueError("API Key is required. Set WA_API_KEY env var or pass api_key parameter.")

    def send_standard(self, to: str, message: str, humanize: bool = True, campaign_id: Optional[int] = None) -> Tuple[bool, Dict[str, Any]]:
        """Send message using the Standard Clean Flat JSON format."""
        payload: Dict[str, Any] = {
            "to": to.strip(),
            "message": message.strip(),
            "humanize": humanize
        }
        if campaign_id is not None:
            payload["campaign_id"] = campaign_id

        headers = {
            "Content-Type": "application/json",
            "X-API-Key": self.api_key
        }
        return self._post(payload, headers)

    def send_meta_format(self, to: str, body: str, preview_url: bool = False) -> Tuple[bool, Dict[str, Any]]:
        """Send message using the Meta WhatsApp Cloud API format."""
        payload = {
            "messaging_product": "whatsapp",
            "recipient_type": "individual",
            "to": to.strip(),
            "type": "text",
            "text": {
                "preview_url": preview_url,
                "body": body.strip()
            }
        }
        headers = {
            "Content-Type": "application/json",
            "Authorization": f"Bearer {self.api_key}"
        }
        return self._post(payload, headers)

    def _post(self, payload: Dict[str, Any], headers: Dict[str, str]) -> Tuple[bool, Dict[str, Any]]:
        """Perform HTTP POST request with timeout and error handling."""
        body_bytes = json.dumps(payload).encode("utf-8")

        if HAS_REQUESTS:
            try:
                resp = requests.post(self.api_url, json=payload, headers=headers, timeout=20)
                try:
                    data = resp.json()
                except Exception:
                    data = {"raw": resp.text}
                is_ok = 200 <= resp.status_code < 300
                return is_ok, data
            except requests.exceptions.RequestException as e:
                return False, {"error": f"Network error: {str(e)}"}

        # Zero-dependency urllib fallback
        req = urllib.request.Request(self.api_url, data=body_bytes, headers=headers, method="POST")
        try:
            with urllib.request.urlopen(req, timeout=20) as resp:
                raw = resp.read().decode("utf-8")
                return True, json.loads(raw)
        except urllib.error.HTTPError as e:
            err_body = e.read().decode("utf-8")
            try:
                return False, json.loads(err_body)
            except Exception:
                return False, {"error": f"HTTP {e.code}: {err_body}"}
        except Exception as e:
            return False, {"error": f"Request failed: {str(e)}"}


def main():
    parser = argparse.ArgumentParser(description="WhatsGateway Python Quickstart")
    parser.add_argument("--to", help="Recipient phone number (e.g. 919876543210)")
    parser.add_argument("--message", help="Message content to send")
    parser.add_argument("--key", default=os.getenv("WA_API_KEY", ""), help="API Key (wa_live_...)")
    parser.add_argument("--url", default=os.getenv("WA_API_URL", WhatsAppHubClient.DEFAULT_API_URL), help="API Endpoint URL")
    parser.add_argument("--format", choices=["standard", "meta"], default="standard", help="Payload format")
    parser.add_argument("--no-humanize", action="store_true", help="Disable humanized typing delays")

    args = parser.parse_args()

    # Prompt interactively if arguments not provided
    api_key = args.key
    if not api_key:
        api_key = input("Enter your API Key (wa_live_...): ").strip()
        if not api_key:
            print("[!] API Key is required to send messages.")
            sys.exit(1)

    api_url = args.url
    recipient = args.to or input("Enter recipient phone number (with country code): ").strip()
    message = args.message or input("Enter message text: ").strip()

    client = WhatsAppHubClient(api_key=api_key, api_url=api_url)

    print(f"\n[*] Sending message via [{args.format.upper()}] format...")
    print(f"[*] Endpoint:  {api_url}")
    print(f"[*] Recipient: {recipient}")

    if args.format == "meta":
        success, response = client.send_meta_format(recipient, message)
    else:
        success, response = client.send_standard(recipient, message, humanize=not args.no_humanize)

    if success:
        print("\n[+] SUCCESS! Message queued for delivery:")
        print(json.dumps(response, indent=2))
    else:
        print("\n[-] FAILED to send message:")
        print(json.dumps(response, indent=2))
        sys.exit(1)


if __name__ == "__main__":
    main()
