import fs from 'fs';
import path from 'path';

const adminDir = 'c:/xampp/htdocs/assistgroup/admin';
const files = fs.readdirSync(adminDir).filter(f => f.endsWith('.php'));

console.log('=== ADMIN FILES IN ASSISTGROUP ===');
for (const f of files) {
  const fullPath = path.join(adminDir, f);
  const stat = fs.statSync(fullPath);
  const content = fs.readFileSync(fullPath, 'utf8');
  
  // Extract actions
  const postMatches = [...content.matchAll(/_POST\[['"]action['"]\]\s*===?\s*['"]([^'"]+)['"]/g)].map(m => m[1]);
  const getMatches = [...content.matchAll(/_GET\[['"]action['"]\]\s*===?\s*['"]([^'"]+)['"]/g)].map(m => m[1]);
  const hasAuthGuard = content.includes('auth_guard.php');
  const isAjax = content.includes('json_encode') || content.includes('application/json');

  console.log(`\n📄 ${f} (${(stat.size / 1024).toFixed(1)} KB)`);
  console.log(`   AuthGuard: ${hasAuthGuard ? 'YES' : 'NO'} | Has JSON/AJAX: ${isAjax ? 'YES' : 'NO'}`);
  if (postMatches.length > 0) console.log(`   POST Actions: ${[...new Set(postMatches)].join(', ')}`);
  if (getMatches.length > 0) console.log(`   GET Actions: ${[...new Set(getMatches)].join(', ')}`);
}
