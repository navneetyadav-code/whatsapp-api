# Direct Execution & Token-Conservation Rule

Always follow these efficiency and token-conservation directives in this repository:

1. **Direct File Targeting**:
   - The project layout is standardized:
     - Dashboard Views: `src/pages/dashboard/<feature>.php`
     - Data Loaders: `src/data/dashboard/<feature>.data.php`
     - PHP Libraries: `src/lib/<Module>.php`
     - Node.js Baileys Worker: `worker/<module>.mjs`
     - Public API Routes: `public/api/<route>.php`
     - Core Layout / Shell: `src/pages/layout.php`, `src/pages/components/`
   - NEVER execute broad recursive directory searches (`Get-ChildItem -Recurse`, deep tree traversals). Target the exact known file immediately.

2. **Zero Unprompted Web Searches**:
   - NEVER invoke `search_web` for internal codebase tasks, PHP/JS/Tailwind syntax, or database queries.
   - Only search the web if the user explicitly asks to look up an external third-party API or library documentation.

3. **Surgical Reads & Edits**:
   - When inspecting files, never dump entire files. Specify `StartLine` and `EndLine` (20–50 lines maximum) around the target function or section.
   - Make edits directly with `edit_file`. Do not generate throwaway scratch inspection scripts when a direct edit or targeted inspection is sufficient.

4. **Ultra-Concise Output**:
   - Do not output repetitive boilerplate explanations or long prologues.
   - Perform the action, verify correctness, and provide a direct, concise summary.
