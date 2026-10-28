import mysql from '../worker/node_modules/mysql2/promise.js';

async function seed() {
  const db = await mysql.createConnection({
    host: '127.0.0.1',
    user: 'root',
    password: '',
    database: 'whatsapp_api'
  });

  const userId = 1;

  // Retrieve existing bot token if available in assistgroup DB
  let botToken = '';
  try {
    const agDb = await mysql.createConnection({
      host: '127.0.0.1',
      user: 'root',
      password: '',
      database: 'assistgroup'
    });
    const [tokens] = await agDb.execute("SELECT token FROM admin_api_tokens WHERE token_name = 'whatsapp-ai-bot-live' AND status = 'active' LIMIT 1");
    if (tokens.length > 0) {
      botToken = tokens[0].token;
    }
    await agDb.end();
  } catch (e) {
    console.log('Assistgroup db note:', e.message);
  }

  const defaultTools = [
    // 1. Built-in Web Search
    {
      tool_slug: 'web_search',
      display_name: '🌐 Web Search (DuckDuckGo)',
      category: 'builtin',
      description: 'Search the live internet for up-to-date facts, current news, technology comparisons, and information not contained in the local knowledge base.',
      parameters_schema: {
        type: 'OBJECT',
        properties: {
          query: { type: 'STRING', description: 'The search query to look up on the web' }
        },
        required: ['query']
      },
      execution_config: {
        handler: 'web_search',
        provider: 'duckduckgo',
        max_results: 3,
        timeout_ms: 4000
      },
      rbac_roles: ['guest', 'customer', 'admin'],
      is_active: 1
    },

    // 2. Built-in Math Evaluator
    {
      tool_slug: 'evaluate_math',
      display_name: '🧮 Precision Math Evaluator',
      category: 'builtin',
      description: 'Evaluate arithmetic calculations, pricing plans, percentage discounts, GST (18%), or project cost totals with 100% precision without hallucinations.',
      parameters_schema: {
        type: 'OBJECT',
        properties: {
          expression: { type: 'STRING', description: 'The math expression to evaluate, e.g. "25000 * 0.85" or "15000 + (15000 * 0.18)"' }
        },
        required: ['expression']
      },
      execution_config: {
        handler: 'evaluate_math'
      },
      rbac_roles: ['guest', 'customer', 'admin'],
      is_active: 1
    },

    // 3. Built-in Time Clock & Business Hours
    {
      tool_slug: 'get_current_time',
      display_name: '🕒 Business Hours & IST Clock',
      category: 'builtin',
      description: 'Get the exact current date, time in Indian Standard Time (IST), day of the week, and check whether company offices are currently open.',
      parameters_schema: {
        type: 'OBJECT',
        properties: {},
        required: []
      },
      execution_config: {
        handler: 'get_current_time',
        timezone: 'Asia/Kolkata',
        open_time: '09:30',
        close_time: '18:30',
        workdays: [1, 2, 3, 4, 5, 6]
      },
      rbac_roles: ['guest', 'customer', 'admin'],
      is_active: 1
    },

    // 4. Built-in Human Escalation
    {
      tool_slug: 'escalate_to_human',
      display_name: '🚨 Human Support Escalation',
      category: 'builtin',
      description: 'Escalate the chat to a human agent, engineer, or supervisor when requested or when an inquiry requires custom human assessment.',
      parameters_schema: {
        type: 'OBJECT',
        properties: {
          reason: { type: 'STRING', description: 'Reason for escalating to human assistance' }
        },
        required: ['reason']
      },
      execution_config: {
        handler: 'escalate_to_human'
      },
      rbac_roles: ['guest', 'customer', 'admin'],
      is_active: 1
    },

    // 5. Custom REST API (Normal API): Live Weather Forecaster (Open-Meteo)
    {
      tool_slug: 'get_weather',
      display_name: '🌤️ Live Weather Forecaster',
      category: 'rest_api',
      description: 'Fetches real-time weather, temperature, humidity, apparent temperature, and wind speed for any location or city using coordinates.',
      parameters_schema: {
        type: 'OBJECT',
        properties: {
          city: { type: 'STRING', description: 'City name (e.g. "Patna", "Delhi", "London", "New York")' },
          latitude: { type: 'NUMBER', description: 'Latitude coordinate (e.g. 25.5941 for Patna, 28.6139 for Delhi)' },
          longitude: { type: 'NUMBER', description: 'Longitude coordinate (e.g. 85.1376 for Patna, 77.2090 for Delhi)' }
        },
        required: ['city', 'latitude', 'longitude']
      },
      execution_config: {
        url: 'https://api.open-meteo.com/v1/forecast?latitude={latitude}&longitude={longitude}&current=temperature_2m,relative_humidity_2m,apparent_temperature,weather_code,wind_speed_10m',
        method: 'GET',
        headers: {
          'Accept': 'application/json'
        },
        timeout_ms: 4500,
        response_path: 'current'
      },
      rbac_roles: ['guest', 'customer', 'admin'],
      is_active: 1
    },

    // 6. Shared Hosting Dual-Environment API: AssistGroup CRM Connector
    {
      tool_slug: 'assistgroup_crm',
      display_name: '🏢 AssistGroup Dual-Env API',
      category: 'shared_env_api',
      description: 'Query official AssistGroup pricing packages, team profiles, and submit client inquiries to the CRM system.',
      parameters_schema: {
        type: 'OBJECT',
        properties: {
          action: { type: 'STRING', description: 'Action to perform: "pricing_plans", "team_overview", "create_inquiry", or "dashboard_metrics"' },
          client_name: { type: 'STRING', description: 'Full name for project inquiry (optional)' },
          client_phone: { type: 'STRING', description: 'Contact phone for project inquiry (optional)' },
          requirement: { type: 'STRING', description: 'Brief description of requested project or service (optional)' }
        },
        required: ['action']
      },
      execution_config: {
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
        timeout_ms: 6000
      },
      rbac_roles: ['guest', 'customer', 'admin'],
      is_active: 1
    }
  ];

  console.log(`Seeding universal tools for user ${userId}...`);

  for (const t of defaultTools) {
    await db.execute(
      `INSERT INTO ai_custom_tools 
       (user_id, tool_slug, display_name, category, description, parameters_schema, execution_config, rbac_roles, is_active)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
       ON DUPLICATE KEY UPDATE
       display_name = VALUES(display_name),
       category = VALUES(category),
       description = VALUES(description),
       parameters_schema = VALUES(parameters_schema),
       execution_config = VALUES(execution_config),
       rbac_roles = VALUES(rbac_roles),
       is_active = VALUES(is_active)`,
      [
        userId,
        t.tool_slug,
        t.display_name,
        t.category,
        t.description,
        JSON.stringify(t.parameters_schema),
        JSON.stringify(t.execution_config),
        JSON.stringify(t.rbac_roles),
        t.is_active
      ]
    );
    console.log(` - Seeded tool: ${t.tool_slug} (${t.category})`);
  }

  await db.end();
  console.log('Universal custom tools seeded successfully!');
}

seed().catch(console.error);
