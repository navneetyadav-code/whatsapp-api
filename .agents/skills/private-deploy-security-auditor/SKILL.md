---
name: private-deploy-security-auditor
description: Performs automated, high-confidence security vulnerability audits, safe surgical fixes, and reporting strictly on the private-deploy git branch without altering application architecture.
---

# Private-Deploy Security Auditor Skill

This skill governs the automated security scanning, vulnerability remediation, and audit reporting process for the WhatsApp Hub repository.

## Non-Negotiable Core Directives

1. **Strict Branch Quarantine (`private-deploy` ONLY):**
   - The agent MUST NEVER make changes, staging, or commits on `main` or any other branch.
   - The agent must always check out and verify the `private-deploy` branch before any analysis or modification:
     ```bash
     git checkout private-deploy
     git pull origin private-deploy
     ```
   - Verify `git branch --show-current` returns `private-deploy`. If it fails to checkout, halt execution immediately with an error log.

2. **Architecture Preservation Constraint:**
   - **DO NOT** rewrite or alter the application architecture.
   - Maintain the existing hybrid stack:
     - PHP 8.2+ Gateway (`/public`, `/src`)
     - Node.js Baileys Worker (`/worker/server.mjs`)
     - MySQL/TiDB database schema (`database.sql`)
     - Supervisord / Docker / Render deployment configuration
   - **Allowed fixes:** Localized, surgical security patches only. Examples:
     - Prepared SQL parameter binding (preventing SQLi)
     - Strict URL/host validation & private IP blocking (preventing SSRF)
     - Directory path normalization with `basename()` / `realpath()` (preventing Path Traversal)
     - Context-aware output escaping via `htmlspecialchars()` (preventing XSS)
     - MIME-type binary validation and extension whitelisting (preventing Unrestricted File Upload)
     - Safe JSON parsing and regex bounding (preventing ReDoS)
     - Safe error masking (preventing Sensitive Information Disclosure)
   - **Forbidden changes:** Framework migrations, introducing large ORMs, altering existing public API endpoint request/response formats, rewriting routing engines, or database table renaming.

3. **High-Confidence Vulnerability Detection:**
   - Follow OWASP standards. Report and patch only confirmed, high-confidence exploitable vulnerabilities where attacker-controlled input reaches a sensitive sink.
   - Do NOT flag or break server-controlled deployment configuration (e.g. database credentials from `.env`, Render runtime environment variables).
   - Ignore theoretical low-risk lints that do not pose an exploitable vulnerability.

## Execution Workflow

### Step 1: Branch Verification & Sync
```bash
git checkout private-deploy
git pull origin private-deploy
```

### Step 2: Comprehensive Codebase Security Audit
Inspect critical attack surfaces:
1. **PHP API Endpoints & Routes (`public/api/`, `src/pages/`, `src/lib/`):**
   - Trace `$_GET`, `$_POST`, `$_REQUEST`, `php://input` flows.
   - Check database queries in `src/lib/Support.php`, `src/lib/Auth.php`, `public/api/*.php` for raw string interpolation into SQL queries.
   - Check SSRF risk in remote media fetchers (verify URLs do not target loopback `127.0.0.1`, `localhost`, `169.254.169.254`, or private subnets `10.0.0.0/8`, `192.168.0.0/16`, `172.16.0.0/12`).
   - Check file upload handlers in `src/lib/Support.php` and `public/uploads/` for extension manipulation and path traversal (`../`).
   - Check sensitive data logging (ensure API keys or passwords are not dumped into public log files).

2. **Node.js Baileys Worker (`worker/server.mjs`):**
   - Check endpoint inputs (`/send`, `/status`, `/connect`).
   - Ensure buffer allocations are bound to reasonable limits.
   - Check asynchronous error handling to prevent unhandled promise rejections that could crash the worker.
   - Check temporary file handling to ensure ephemeral media files are always cleaned up in `finally` blocks.

### Step 3: Apply Surgical Remediation
- Make direct, minimal edits to remediate the vulnerability.
- Maintain existing coding conventions, style, and comments.

### Step 4: Syntax & Integrity Verification
Before staging any changes, test modified files:
- For PHP files:
  ```bash
  php -l <modified_file.php>
  ```
- For Node.js files:
  ```bash
  node --check <modified_file.mjs>
  ```
- If any syntax error is reported, fix it immediately or revert the change.

### Step 5: Generate Timestamped Audit Report
Create a markdown report at:
`reports/security-audit/audit-YYYY-MM-DD-HHmm.md`

The report must contain:
- **Audit Timestamp & Branch:** Date/time and commit SHA on `private-deploy`.
- **Summary of Findings:** Number of high/medium vulnerabilities identified.
- **Vulnerability Details:**
  - Vulnerability Type (e.g., CWE-89 SQLi, CWE-918 SSRF, CWE-22 Path Traversal).
  - Target File & Line Range.
  - Vulnerability Mechanism (how an attacker could exploit it).
  - Remediation Applied (explanation of the fix).
  - Verification Status (PHP/Node syntax test results).
- **If No Vulnerabilities Found:** Clearly log "Clean Scan - Zero high-confidence vulnerabilities detected".

### Step 6: Git Staging, Commit & Push
If changes or reports were made:
```bash
git add reports/security-audit/
git add <remediated_files...>
git commit -m "chore(security): automated vulnerability patch and audit report [skip ci]"
git push origin private-deploy
```
If no changes were required other than a clean scan report, commit the clean report so there is an immutable record of the scan.
