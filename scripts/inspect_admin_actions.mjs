import fs from 'fs';
import path from 'path';

const adminDir = 'c:/xampp/htdocs/assistgroup/admin';
const targetFiles = [
  'adminpanel.php',
  'users.php',
  'inquiries.php',
  'files.php',
  'pricing.php',
  'team.php',
  'settings.php',
  'audit-logs.php',
  'cloudinary-accounts.php',
  'email-templates.php',
  'whatsapp-templates.php',
  'content.php'
];

for (const f of targetFiles) {
  const fullPath = path.join(adminDir, f);
  if (!fs.existsSync(fullPath)) continue;
  const content = fs.readFileSync(fullPath, 'utf8');

  // Look for if ($_SERVER['REQUEST_METHOD'] === 'POST') or isset($_POST[...])
  const postConditions = [...content.matchAll(/if\s*\((?:isset\(\$_POST\['([^']+)'\]\)|\$_POST\['([^']+)'\])/g)]
    .map(m => m[1] || m[2]);

  // Look for switch($_POST['action']) or switch($action)
  const switchCases = [...content.matchAll(/case\s+['"]([^'"]+)['"]\s*:/g)].map(m => m[1]);

  console.log(`\n================ ${f} ================`);
  console.log('Direct $_POST keys detected:', [...new Set(postConditions)].slice(0, 15).join(', '));
  if (switchCases.length > 0) {
    console.log('Action/Case handlers:', [...new Set(switchCases)].slice(0, 15).join(', '));
  }
}
