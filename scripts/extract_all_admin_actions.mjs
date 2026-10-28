import fs from 'fs';
import path from 'path';

const files = [
  'adminpanel.php',
  'users.php',
  'inquiries.php',
  'files.php',
  'pricing.php',
  'team.php',
  'settings.php',
  'cloudinary-accounts.php',
  'email-templates.php',
  'whatsapp-templates.php',
  'content.php',
  'audit-logs.php'
];

const results = {};

for (const file of files) {
  const filePath = path.join('c:/xampp/htdocs/assistgroup/admin', file);
  if (!fs.existsSync(filePath)) continue;
  const content = fs.readFileSync(filePath, 'utf8');

  const actions = new Set();
  const re = /(?:\$action\s*===?\s*['"]([^'"]+)['"]|['"]action['"]\s*===?\s*['"]([^'"]+)['"]|\$_POST\['action'\]\s*===?\s*['"]([^'"]+)['"]|\$_GET\['action'\]\s*===?\s*['"]([^'"]+)['"])/g;
  let match;
  while ((match = re.exec(content)) !== null) {
    const act = match[1] || match[2] || match[3] || match[4];
    if (act) actions.add(act);
  }
  results[file] = Array.from(actions);
}

console.log(JSON.stringify(results, null, 2));
