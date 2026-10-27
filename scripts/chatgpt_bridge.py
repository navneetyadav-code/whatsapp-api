#!/usr/bin/env python3
"""
ChatGPT Automation Bridge via Chrome Playwright
------------------------------------------------
Connects to ChatGPT in a real Chrome persistent session, delivers prompt contexts,
monitors generation in real time, and extracts clean markdown responses.
"""

import os
import sys
import time
import argparse
from playwright.sync_api import sync_playwright

PROFILE_DIR = os.path.join(
    os.environ.get("LOCALAPPDATA", os.path.expanduser("~")),
    "Google",
    "Chrome",
    "ChatGPT_Agent_Profile"
)

MANAGER_ROLE_PROMPT = """
You are acting as the LEAD ARCHITECT, PROJECT MANAGER, and CODE QUALITY GUARDIAN for 'WhatsGateway' (an open-source, enterprise-grade WhatsApp API & Live Chat gateway).

Project Context:
- Tech Stack: PHP 8.2 (MVC/Procedural backend, Apache mod_php), Node.js 20 (Baileys WhatsApp worker daemon), MySQL 8.0, Vanilla JS frontend with Bootstrap/Tailwind aesthetics.
- Architecture: Zero-cloud vector RAG (embeddings cache + cosine similarity), Multi-provider LLM router (Gemini 2.0 Flash primary, Groq Llama 3.3 70B secondary, OpenRouter tertiary), Hardware WebAudio Buzzer + Desktop Push Notifications for human takeover.
- Current Objectives:
  1. Complete In-Browser WebAudio Buzzer & Native Desktop Push Notifications on frustrated/angry customer cues.
  2. Multi-LLM provider cascade engine with zero-extra-infrastructure vector RAG for customer company knowledge.
  3. Dashboard management view for AI Personas & FAQ documents.
  4. Ensure zero security vulnerabilities, atomic Git history, and production-hardened reliability.

Your Responsibility:
- Review architectural decisions, provide detailed code patterns, critique implementations, and ensure zero placeholders or shallow code.
- Respond with: "Lead Architect ready. What phase or code diff shall we review?"
"""

def send_chatgpt_query(prompt_text: str, timeout_sec: int = 120, headless: bool = False) -> str:
    """
    Launches persistent Chrome session, submits prompt to ChatGPT, waits for completion,
    and returns assistant's extracted response text.
    """
    os.makedirs(PROFILE_DIR, exist_ok=True)
    
    with sync_playwright() as p:
        context = p.chromium.launch_persistent_context(
            PROFILE_DIR,
            channel="chrome",
            headless=headless,
            viewport=None,
            args=[
                "--start-maximized",
                "--disable-blink-features=AutomationControlled"
            ]
        )
        
        page = context.pages[0] if context.pages else context.new_page()
        page.goto("https://chatgpt.com", wait_until="domcontentloaded")
        time.sleep(2)
        
        # Locate composer input
        textarea = page.locator(
            'textarea[name="prompt"], textarea#mobile-composer-prompt, textarea[placeholder*="Ask ChatGPT"]'
        ).first
        
        if textarea.count() == 0:
            raise RuntimeError("Could not find ChatGPT input textarea on page.")
        
        textarea.click()
        textarea.fill(prompt_text)
        time.sleep(0.5)
        
        # Click send button
        send_btn = page.locator('button[aria-label="Send message"], button[aria-label*="Send"]').first
        if send_btn.count() > 0 and send_btn.is_visible():
            send_btn.click()
        else:
            textarea.press("Enter")
            
        print("[+] Prompt dispatched to ChatGPT. Monitoring generation...", flush=True)
        
        # Wait for generation to start and finish
        time.sleep(3)
        start_time = time.time()
        while time.time() - start_time < timeout_sec:
            stop_btn = page.locator('button[data-testid="stop-button"], button[aria-label*="Stop"]').count()
            busy_nodes = page.locator('div[aria-busy="true"]').count()
            
            # When stop button and busy indicator are both gone after initial delay, response is complete
            if stop_btn == 0 and busy_nodes == 0 and (time.time() - start_time > 3):
                print(f"[+] Response completed in {int(time.time() - start_time)}s.", flush=True)
                break
            time.sleep(1)
        
        time.sleep(1)
        # Extract response from DOM
        response = page.evaluate("""() => {
            const assistantNodes = document.querySelectorAll('[data-assistant-markdown]');
            if (assistantNodes.length > 0) {
                return assistantNodes[assistantNodes.length - 1].innerText;
            }
            const turns = document.querySelectorAll('[data-message-author-role="assistant"], .agent-turn');
            if (turns.length > 0) {
                return turns[turns.length - 1].innerText;
            }
            return "";
        }""")
        
        context.close()
        return response.strip()

def main():
    parser = argparse.ArgumentParser(description="ChatGPT Playwright Bridge")
    parser.add_argument("prompt", nargs="?", default="", help="Prompt string to send")
    parser.add_argument("--file", "-f", help="Read prompt from file")
    parser.add_argument("--output", "-o", help="Write response to file")
    parser.add_argument("--init-manager", action="store_true", help="Prime ChatGPT with Lead Architect & Manager role")
    parser.add_argument("--headless", action="store_true", help="Run Chrome headlessly")
    
    args = parser.parse_args()
    
    if args.init_manager:
        prompt = MANAGER_ROLE_PROMPT
    elif args.file:
        with open(args.file, "r", encoding="utf-8") as f:
            prompt = f.read()
    elif args.prompt:
        prompt = args.prompt
    else:
        print("Error: No prompt provided. Use positional prompt, --file, or --init-manager.", file=sys.stderr)
        sys.exit(1)
        
    print("[*] Launching Chrome ChatGPT Bridge...")
    reply = send_chatgpt_query(prompt, headless=args.headless)
    
    if args.output:
        with open(args.output, "w", encoding="utf-8") as f:
            f.write(reply)
        print(f"[+] Response written to {args.output}")
    else:
        print("\n" + "=" * 50)
        print(reply)
        print("=" * 50 + "\n")

if __name__ == "__main__":
    main()
