# Direct Execution & Token-Conservation Directives

1. **Target Known Paths Directly**:
   - Views: `src/pages/dashboard/<feature>.php`
   - Data: `src/data/dashboard/<feature>.data.php`
   - Libs: `src/lib/<Module>.php`
   - Worker: `worker/<module>.mjs`
   - Routes: `public/api/<route>.php`
   - Layout: `src/pages/layout.php`, `src/pages/components/`
   - No broad recursive scans or directory dumps.

2. **Zero Unprompted Web Searches**:
   - Do not use `search_web` for codebase tasks or standard language syntax.

3. **Surgical Reads & Edits**:
   - Use tight line ranges (20-50 lines) when reading.
   - Use `edit_file` directly without generating throwaway test files.

4. **Concise Communication**:
   - Zero boilerplate prose. Make the changes, verify, and respond directly.
