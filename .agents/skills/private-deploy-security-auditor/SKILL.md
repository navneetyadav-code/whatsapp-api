---
name: private-deploy-security-auditor
description: Performs automated, high-confidence security audits, surgical vulnerability remediation, regression test verification, and audit reporting strictly on the private-deploy git branch. Use when asked to audit security, harden private deploy, scan vulnerabilities, or review OWASP compliance.
---

# Private-Deploy Security Auditor Skill

Governs automated security audits, vulnerability remediation, regression verification, and audit reporting for the WhatsApp API Hub strictly on the `private-deploy` branch.

## 1. Non-Negotiable Directives

1. **Strict Branch Quarantine (`private-deploy` ONLY):**
   - **NEVER** modify, stage, or commit code to `main` or any other branch.
   - Always verify a clean working tree before switching:
     ```bash
     git status --porcelain
     git checkout private-deploy
     git pull origin private-deploy
     ```
   - Verify `git branch --show-current` outputs `private-deploy`. If checkout fails, abort immediately.
   - **Never force push (`git push -f`).**

2. **Architecture & Contract Preservation:**
   - **Zero architectural changes:** Do not introduce large frameworks, ORMs, or rewrite routing engines.
   - Maintain the hybrid architecture:
     - PHP 8.2+ Gateway (`/public`, `/src`, `/config`)
     - Node.js Baileys Worker (`/worker`)
     - MySQL / TiDB Database Schema (`database.sql`, `migrations/`)
     - Docker / Supervisord / Render configurations
   - **Zero API Contract Breaks:** Preserve existing request/response schemas for `/api/send.php`, `/api/messages.php`, `/api/health.php`, and `/worker/server.mjs`.

3. **High-Confidence Vulnerability Remediation Only:**
   - Focus on verifiable, high/medium-severity vulnerabilities (OWASP Top 10) where attacker-controlled input reaches an exploitable sink.
   - Do NOT modify valid server-side environment variables (`.env`, Render configurations).
   - Reject purely cosmetic lints that do not remediate an actual security flaw.

---

## 2. Attack Surface Checklist (WhatsApp Gateway Specific)

When performing an audit, systematically inspect:

### A. PHP Gateway & Web Endpoints (`public/`, `src/pages/`, `src/lib/`, `config/`)
- [ ] **SQL Injection:** Verify all queries in `src/lib/Support.php`, `src/lib/Auth.php`, `public/api/*.php` use prepared PDO statements with bound parameters (`:param` / `?`).
- [ ] **SSRF & DNS Rebinding:** Ensure media fetchers block loopback (`127.0.0.0/8`, `::1`), private ranges (`10.0.0.0/8`, `172.16.0.0/12`, `192.168.0.0/16`), link-local/cloud metadata (`169.254.169.254`, `metadata.google.internal`), and validate IP post-DNS resolution.
- [ ] **Path Traversal & File Uploads:** Ensure uploaded files are validated via MIME binary headers (not solely extension), normalized via `basename()`, and stored outside public script execution paths.
- [ ] **CSV / Formula Injection (DDE):** Ensure contacts export and import sanitize cells beginning with `=`, `+`, `-`, `@`, `\t`, `\r` by prefixing a single quote (`'`).
- [ ] **Authentication & Timing:** Ensure token/hash comparisons use `hash_equals()`. Ensure API keys in GET query strings are rejected by default (`security.allow_query_auth = false`).
- [ ] **Session & Access Control:** Verify CLI utilities (`scripts/*.php`, `setup.php`) enforce `PHP_SAPI === 'cli'`. Ensure session cookies set `HttpOnly`, `Secure`, and `SameSite=Lax/Strict`.
- [ ] **Public Directory Isolation:** Verify root `.htaccess` and web server configs block direct HTTP access to `config/`, `src/`, `worker/`, `scripts/`, `tests/`, and `.env`.

### B. Node.js Baileys Worker (`worker/server.mjs`, `worker/http-diagnostics.mjs`)
- [ ] **Network Binding & Worker Auth:** Ensure worker listens on loopback (`127.0.0.1`) unless proxy is configured. Validate worker auth tokens using `crypto.timingSafeEqual()`.
- [ ] **Request Body Null-Safety:** Safeguard JSON parsers and handlers against null dereferencing (`req.body?.field`).
- [ ] **Webhook Signatures & Replays:** Verify inbound webhooks validate HMAC signatures (`X-Hub-Signature-256`) and enforce a timestamp freshness window (≤ 5 minutes).
- [ ] **Ephemeral Media Cleanup:** Ensure temporary files downloaded by Baileys are allocated via `crypto.randomUUID()` and cleaned up in `finally` blocks.
- [ ] **DoS & Stream Bounds:** Enforce maximum payload and stream buffer size limits to prevent worker memory exhaustion.

### C. Secret Hygiene & Supply Chain
- [ ] **Secret Hygiene Scan:** Confirm no live keys (`wa_live_[0-9a-f]{24,}`), DB passwords, SMTP credentials, or private keys exist in tracked files.
- [ ] **Dependency Audit:** Check for critical dependency vulnerabilities:
  ```bash
  cd worker && npm audit --omit=dev && cd ..
  ```

---

## 3. Step-by-Step Execution Workflow

```
[1. Sync & Verify Branch] -> [2. Audit Attack Surfaces] -> [3. Surgical Patch]
          |                                                       |
[6. Commit & Push] <--- [5. Generate Report] <--- [4. Full Test Verification]
                                                          | (Failure)
                                                    [Rollback & Abort]
```

### Step 1: Branch Verification & Sync
```bash
git checkout private-deploy
git pull origin private-deploy
git branch --show-current
```

### Step 2: Codebase Security Audit
Inspect files according to Section 2. Identify vulnerable sources, sinks, and missing guards.

### Step 3: Apply Surgical Remediation
- Apply the minimal viable code patch to eliminate the exploit.
- Preserve existing coding conventions, style, and comments.

### Step 4: Verification & Automated Regression Testing
Every patch MUST pass all of the following:

1. **Syntax Integrity:**
   ```bash
   php -l <modified_file.php>
   node --check <modified_file.mjs>
   ```
2. **PHP Security & Functional Test Suites:**
   ```bash
   php tests/secret-hygiene.test.php
   php tests/security-features.test.php
   php tests/api-auth-security.test.php
   php tests/cli-sapi-and-access-control.test.php
   php tests/ssrf-and-traversal-security.test.php
   php tests/upload-security.test.php
   php tests/client-ip-spoofing.test.php
   php tests/setup-protection.test.php
   ```
3. **Node.js Test Suites:**
   ```bash
   node --test tests/*.mjs
   ```
4. **Failsafe Rollback Trigger:**
   If any syntax or regression test fails and cannot be immediately rectified with a clean fix:
   ```bash
   git restore <modified_file>
   git clean -fd
   ```
   *Never commit code that fails any test suite.*

### Step 5: Generate Timestamped Audit Report
Save report to: `reports/security-audit/audit-YYYY-MM-DD-HHmm.md`

Use this structured format:
```markdown
# Security Vulnerability & Audit Report

- **Date / Time:** YYYY-MM-DD HH:mm:ss IST
- **Target Branch:** `private-deploy`
- **Commit SHA:** `<commit-sha>`
- **Scan Status:** Remediation Applied & Verified | Clean Scan

## Executive Summary
<Brief summary of scope, findings, and remediation status>

## Identified Vulnerabilities & Applied Remediation
### 1. <Title>
- **Classification:** CWE-XXX / OWASP AXX:2021
- **Severity:** Critical | High | Medium | Low (CVSS score)
- **File Affected:** `<path>` (Lines X–Y)
- **Vulnerability Mechanism:** <How it could be exploited>
- **Remediation Applied:** <Exact fix explanation>
- **Verification Status:** <Test output confirmation>

*(If zero vulnerabilities: "Clean Scan - Zero high-confidence vulnerabilities detected")*

## Architectural Integrity & Verification Results
- **Syntax Checks:** `php -l` and `node --check` passed
- **Regression Suites:** All PHP and Node.js tests passed (X/X passed)
```

### Step 6: Git Staging, Commit & Push
```bash
git add reports/security-audit/
git add <remediated_files...>
git commit -m "chore(security): automated vulnerability patch and audit report [skip ci]"
git push origin private-deploy
```
*(If clean scan with no code changes, commit the audit report so there is an immutable record of the scan.)*
