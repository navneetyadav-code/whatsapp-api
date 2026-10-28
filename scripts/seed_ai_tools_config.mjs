import mysql from '../worker/node_modules/mysql2/promise.js';
import crypto from 'crypto';

async function main() {
  console.log('--- SEEDING AI TOOLS ENGINE CONFIGURATION ---');

  // Connect to assistgroup DB
  const agDb = await mysql.createConnection({
    host: '127.0.0.1',
    user: 'root',
    password: '',
    database: 'assistgroup'
  });

  // Check admin
  const [admins] = await agDb.query('SELECT id, Admin_Name, email FROM admin_login WHERE id = 1 LIMIT 1');
  if (admins.length === 0) {
    throw new Error('Admin ID 1 not found in assistgroup');
  }

  // Generate permanent bot token
  const rawSecret = crypto.randomBytes(32).toString('hex');
  const botToken = `ast_live_${rawSecret}`;
  const tokenPrefix = botToken.substring(0, 16);
  const tokenHash = crypto.createHash('sha256').update(botToken).digest('hex');

  // Insert token for WhatsApp AI Agent
  await agDb.execute(
    `INSERT INTO admin_api_tokens (admin_id, name, token_prefix, token_hash, scopes, expires_at)
     VALUES (?, ?, ?, ?, ?, ?)`,
    [1, 'WhatsApp AI Agent Bot Token', tokenPrefix, tokenHash, JSON.stringify(['*']), '2036-12-31 23:59:59']
  );
  console.log(`Generated AssistGroup Bot Token: ${tokenPrefix}****************`);
  await agDb.end();

  // Connect to whatsapp_api DB
  const waDb = await mysql.createConnection({
    host: '127.0.0.1',
    user: 'root',
    password: '',
    database: 'whatsapp_api'
  });

  const defaultConfig = {
    enabled: true,
    admin_phones: ['+918581065107', '+919507286092', '8581065107', '9507286092'],
    tools: {
      web_search: {
        enabled: true,
        provider: 'duckduckgo',
        max_results: 3,
        timeout_ms: 3500,
        roles: ['guest', 'customer', 'admin']
      },
      assistgroup_api: {
        enabled: true,
        active_env: 'local',
        environments: {
          local: {
            base_url: 'http://localhost/assistgroup/api/admin/v1',
            bearer_token: botToken
          },
          live: {
            base_url: 'https://assistgroup.gt.tc/api/admin/v1',
            bearer_token: botToken
          }
        },
        actions: {
          get_services_pricing: { enabled: true, roles: ['guest', 'customer', 'admin'] },
          create_inquiry: { enabled: true, roles: ['guest', 'customer', 'admin'] },
          lookup_inquiries: { enabled: true, roles: ['customer', 'admin'] },
          get_team_overview: { enabled: true, roles: ['guest', 'customer', 'admin'] },
          admin_dashboard_stats: { enabled: true, roles: ['admin'] }
        }
      },
      evaluate_math: {
        enabled: true,
        roles: ['guest', 'customer', 'admin']
      },
      get_current_time: {
        enabled: true,
        timezone: 'Asia/Kolkata',
        office_hours: {
          start: '09:30',
          end: '18:30',
          work_days: [1, 2, 3, 4, 5, 6]
        },
        roles: ['guest', 'customer', 'admin']
      },
      escalate_to_human: {
        enabled: true,
        roles: ['guest', 'customer', 'admin']
      }
    }
  };

  const jsonConfig = JSON.stringify(defaultConfig);

  // Update existing ai_agent_settings rows
  const [res] = await waDb.execute(
    'UPDATE ai_agent_settings SET tools_config = ? WHERE tools_config IS NULL OR tools_config = ""',
    [jsonConfig]
  );
  console.log(`Updated ai_agent_settings rows: ${res.affectedRows}`);

  await waDb.end();
  console.log('✅ Phase 1: Database & Seed Configuration Complete!');
}

main().catch(err => {
  console.error('Seed error:', err);
  process.exit(1);
});
