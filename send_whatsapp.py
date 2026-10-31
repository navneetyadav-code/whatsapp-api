#!/usr/bin/env python3
"""
Interactive WhatsApp Message Sender CLI
Send WhatsApp messages through your WhatsApp API Hub (Local or Cloud).

Author: Navneet Kumar Yadav
"""

import sys
import os
import re
import json

# Ensure UTF-8 output on Windows consoles
if hasattr(sys.stdout, "reconfigure"):
    try:
        sys.stdout.reconfigure(encoding="utf-8", errors="replace")
        sys.stderr.reconfigure(encoding="utf-8", errors="replace")
    except Exception:
        pass

# Try importing requests, fallback to urllib if not installed
try:
    import requests
    HAS_REQUESTS = True
except ImportError:
    import urllib.request
    import urllib.error
    HAS_REQUESTS = False

CONFIG_FILE = os.path.join(os.path.dirname(os.path.abspath(__file__)), ".whatsapp_cli.json")
DEFAULT_CLOUD_URL = "https://whatsapp-api-xepo.onrender.com/api/send.php"
DEFAULT_LOCAL_URL = "http://localhost/whatsapp-api/public/api/send.php"


def load_config() -> dict:
    """Load cached API configuration from file or .env."""
    config = {
        "api_url": DEFAULT_CLOUD_URL,
        "api_key": ""
    }

    # Check if .env file exists in the directory
    env_path = os.path.join(os.path.dirname(os.path.abspath(__file__)), ".env")
    if os.path.isfile(env_path):
        try:
            with open(env_path, "r", encoding="utf-8") as f:
                for line in f:
                    line = line.strip()
                    if line.startswith("SYSTEM_API_KEY="):
                        val = line.split("=", 1)[1].strip().strip('"').strip("'")
                        if val:
                            config["api_key"] = val
                    elif line.startswith("APP_PUBLIC_URL="):
                        val = line.split("=", 1)[1].strip().strip('"').strip("'")
                        if val:
                            config["api_url"] = f"{val.rstrip('/')}/api/send.php"
        except Exception:
            pass

    # Override with user config cache if present
    if os.path.isfile(CONFIG_FILE):
        try:
            with open(CONFIG_FILE, "r", encoding="utf-8") as f:
                saved = json.load(f)
                if saved.get("api_url"):
                    config["api_url"] = saved["api_url"]
                if saved.get("api_key"):
                    config["api_key"] = saved["api_key"]
        except Exception:
            pass

    return config


def save_config(config: dict) -> None:
    """Save API configuration for future runs."""
    try:
        with open(CONFIG_FILE, "w", encoding="utf-8") as f:
            json.dump(config, f, indent=2)
    except Exception:
        pass


def clean_phone_number(raw_phone: str) -> str:
    """Normalize phone number to international digits-only format."""
    digits = re.sub(r"[^\d]", "", raw_phone.strip())
    # If 10 digits entered without country code, default to India (+91)
    if len(digits) == 10:
        digits = "91" + digits
    return digits


def send_message(api_url: str, api_key: str, recipient: str, message: str) -> tuple[bool, dict]:
    """Send message payload to WhatsApp API Gateway."""
    payload = {
        "to": recipient,
        "message": message,
        "humanize": True
    }
    headers = {
        "Content-Type": "application/json",
        "X-API-Key": api_key,
        "User-Agent": "WhatsApp-CLI-Sender/1.0"
    }

    if HAS_REQUESTS:
        try:
            resp = requests.post(api_url, json=payload, headers=headers, timeout=20)
            try:
                data = resp.json()
            except Exception:
                data = {"ok": False, "error": f"Invalid response from server: {resp.text[:200]}"}
            return (resp.status_code == 200 and data.get("ok", False)), data
        except requests.exceptions.RequestException as e:
            return False, {"ok": False, "error": f"Connection error: {str(e)}"}
    else:
        # Fallback using urllib.request
        try:
            req = urllib.request.Request(
                api_url,
                data=json.dumps(payload).encode("utf-8"),
                headers=headers,
                method="POST"
            )
            with urllib.request.urlopen(req, timeout=20) as resp:
                data = json.loads(resp.read().decode("utf-8"))
                return (resp.status == 200 and data.get("ok", False)), data
        except urllib.error.HTTPError as e:
            try:
                data = json.loads(e.read().decode("utf-8"))
            except Exception:
                data = {"ok": False, "error": f"HTTP Error {e.code}: {e.reason}"}
            return False, data
        except Exception as e:
            return False, {"ok": False, "error": f"Connection error: {str(e)}"}


def main():
    print("=" * 60)
    print("   [+] WhatsApp API Hub - Interactive Message Sender CLI")
    print("   Author: Navneet Kumar Yadav")
    print("=" * 60)

    config = load_config()

    # 1. Select or confirm API Endpoint
    print("\n[1] Select Target API Endpoint:")
    print(f"  1. Render Cloud (Live): {DEFAULT_CLOUD_URL}")
    print(f"  2. Local XAMPP (Local): {DEFAULT_LOCAL_URL}")
    print(f"  3. Custom URL")
    
    current_default = "1" if "render.com" in config["api_url"] else "2"
    choice = input(f"Select option [1/2/3, default: {current_default}]: ").strip() or current_default

    if choice == "1":
        config["api_url"] = DEFAULT_CLOUD_URL
    elif choice == "2":
        config["api_url"] = DEFAULT_LOCAL_URL
    elif choice == "3":
        custom_url = input(f"Enter custom API URL [{config['api_url']}]: ").strip()
        if custom_url:
            config["api_url"] = custom_url

    # 2. Configure API Key
    if not config["api_key"]:
        print("\n[2] API Key Configuration:")
        api_key = input("Enter your X-API-Key (e.g. wa_live_...): ").strip()
        while not api_key:
            print("API Key cannot be empty!")
            api_key = input("Enter your X-API-Key: ").strip()
        config["api_key"] = api_key
    else:
        masked_key = config["api_key"][:15] + "..." + config["api_key"][-4:]
        change = input(f"\n[2] API Key currently set to: {masked_key}\nPress Enter to keep, or type a new key: ").strip()
        if change:
            config["api_key"] = change

    save_config(config)

    print("\n" + "-" * 60)
    print(f"[*] Target Endpoint: {config['api_url']}")
    print("-" * 60)

    # Message sending loop
    while True:
        print("\n--- Send New Message ---")
        
        # Phone Number input
        raw_phone = input("Enter Recipient Phone (e.g. 9876543210 or +919876543210): ").strip()
        while not raw_phone:
            print("Phone number cannot be empty!")
            raw_phone = input("Enter Recipient Phone: ").strip()

        clean_phone = clean_phone_number(raw_phone)
        print(f"-> Destination Number: +{clean_phone}")

        # Message Text input
        print("\nEnter Message Text:")
        print("(Type your message and press Enter. For newline, use \\n)")
        message_text = input("Message: ").strip()
        while not message_text:
            print("Message text cannot be empty!")
            message_text = input("Message: ").strip()

        # Handle escaped newlines
        message_text = message_text.replace("\\n", "\n")

        print("\n[*] Sending message via API Gateway...")
        success, result = send_message(config["api_url"], config["api_key"], clean_phone, message_text)

        print("\n" + "=" * 45)
        if success:
            print("  [SUCCESS] MESSAGE QUEUED / SENT!")
            print("=" * 45)
            print(f"  * Message ID : {result.get('message_id', 'N/A')}")
            print(f"  * Status     : {result.get('status', 'queued')}")
            print(f"  * Recipient  : +{clean_phone}")
            if result.get("request_id"):
                print(f"  * Request ID : {result.get('request_id')}")
        else:
            print("  [FAILED] MESSAGE NOT SENT")
            print("=" * 45)
            error_msg = result.get("error") or result.get("message") or str(result)
            print(f"  * Error: {error_msg}")
            print("\n  [Tip]:")
            print("  - Check that your WhatsApp device is connected and Online.")
            print("  - Check that your API Key is active in Dashboard -> API Keys.")
            print("  - Ensure the recipient number is registered on WhatsApp.")

        print("=" * 45)

        # Loop prompt
        again = input("\nDo you want to send another message? (y/n) [y]: ").strip().lower()
        if again in ["n", "no", "q", "exit"]:
            print("\nExiting. Thank you for using WhatsApp API Hub CLI! Goodbye.\n")
            break


if __name__ == "__main__":
    try:
        main()
    except KeyboardInterrupt:
        print("\n\nOperation cancelled by user. Exiting.")
        sys.exit(0)
