import mysql from '../worker/node_modules/mysql2/promise.js';

async function main() {
  console.log('⚡ Updating AssistGroup Enterprise Tools for AI Assistant...');

  const db = await mysql.createConnection({
    host: '127.0.0.1',
    user: 'root',
    password: '',
    database: 'whatsapp_api'
  });

  const token = 'ast_live_99584846bd475b06705f88383b0b523e5a50f18b744729376e1501ca3320645f';

  const executionConfig = {
    active_env: 'local',
    timeout_ms: 8000,
    environments: {
      local: {
        base_url: 'http://localhost/assistgroup/api/admin/v1',
        bearer_token: token
      },
      live: {
        base_url: 'https://assistgroup.gt.tc/api/admin/v1',
        bearer_token: token
      }
    }
  };

  const parametersSchema = {
    type: 'OBJECT',
    required: ['action'],
    properties: {
      action: {
        type: 'STRING',
        description: "The AssistGroup action to perform: 'dashboard_metrics' (overall users, revenue, inquiries, files, security summary), 'dashboard_growth' (user growth & signup velocity), 'dashboard_system' (server resource usage, memory, disk, and load), 'pricing_plans' (all official subscription packages, prices, features), 'god_mode_list' (active God-Mode grants), 'team_overview' (team roster, designations, bios), 'list_inquiries' (view recent client leads & inquiries), 'get_inquiry' (detailed inquiry by inquiry_id), 'create_inquiry' (add new client lead/inquiry), 'update_inquiry_status' (update inquiry status), 'list_users' (search registered users), 'get_user' (view user profile by user_id), 'list_files' (storage files and download stats), 'cloudinary_pool' (multi-account Cloudinary storage metrics), 'audit_logs' (security audit summary and critical events)"
      },
      query: {
        type: 'STRING',
        description: 'Optional search keyword for searching users or inquiries'
      },
      inquiry_id: {
        type: 'INTEGER',
        description: 'Inquiry ID for get_inquiry or update_inquiry_status'
      },
      user_id: {
        type: 'INTEGER',
        description: 'User ID for get_user'
      },
      status: {
        type: 'STRING',
        description: "New status for update_inquiry_status: 'new', 'in_progress', 'resolved', 'closed'"
      },
      client_name: {
        type: 'STRING',
        description: 'Client full name for create_inquiry'
      },
      client_phone: {
        type: 'STRING',
        description: 'Client phone number for create_inquiry'
      },
      client_email: {
        type: 'STRING',
        description: 'Client email address for create_inquiry'
      },
      service: {
        type: 'STRING',
        description: "Service name for create_inquiry (e.g. 'Web Development', 'SEO Audit', 'Custom Software')"
      },
      requirement: {
        type: 'STRING',
        description: 'Detailed project requirement or message for create_inquiry'
      }
    }
  };

  const displayName = '🏢 AssistGroup Enterprise Suite';
  const description = 'Official AssistGroup Enterprise API suite. Use this tool whenever the Boss or user asks about AssistGroup metrics, dashboard statistics, server health, registered users, client inquiries/leads, subscription pricing plans, team roster, uploaded files, Cloudinary storage pool, or security audit logs.';
  const rbacRoles = ['guest', 'customer', 'admin'];

  // Update for all users in ai_custom_tools
  await db.execute(
    `UPDATE ai_custom_tools
     SET display_name = ?,
         description = ?,
         parameters_schema = ?,
         execution_config = ?,
         rbac_roles = ?,
         is_active = 1
     WHERE tool_slug = 'assistgroup_crm'`,
    [
      displayName,
      description,
      JSON.stringify(parametersSchema),
      JSON.stringify(executionConfig),
      JSON.stringify(rbacRoles)
    ]
  );
  console.log('✔ Updated assistgroup_crm tool in ai_custom_tools with full API suite & Bearer token.');

  // Update Personal Assistant system prompt in ai_agent_settings for user 1 & 2
  const personalPrompt = `You are the dedicated, highly capable Personal AI Executive Assistant & Chief of Staff to your Boss.

🎯 CORE RESPONSIBILITIES & DIRECTIVES:
1. Executive Role: You work directly and exclusively for your Boss. Address them respectfully as "Boss" or "Sir". Be proactive, loyal, dependable, and efficient.
2. 24/7 Availability: You are active 24/7 around the clock. You have no office hour restrictions or closed periods.
3. Full AssistGroup Enterprise Access:
   • You have direct executive access to the entire AssistGroup platform via the "assistgroup_crm" tool.
   • Whenever the Boss asks about AssistGroup metrics, dashboard stats, server resources, registered users, client inquiries/leads, pricing plans, team members, files, or audit logs, proactively call "assistgroup_crm" with the appropriate action.
   • Available AssistGroup actions: "dashboard_metrics", "dashboard_growth", "dashboard_system", "pricing_plans", "god_mode_list", "team_overview", "list_inquiries", "get_inquiry", "create_inquiry", "update_inquiry_status", "list_users", "get_user", "list_files", "cloudinary_pool", "audit_logs".
4. Unrestricted General Intelligence: Assist with ANY request across:
   • Daily task management, to-do lists, reminders, notes, and schedules.
   • Live web search and fact verification (using the "web_search" tool).
   • Real-time weather checks and forecasts (using the "get_weather" tool).
   • Precise math, conversion, financial, and formula calculations (using the "evaluate_math" tool).
   • Current time, date, and calendar verification (using the "get_current_time" tool).
   • Writing, copywriting, proofreading, emails, and WhatsApp message drafting.
   • Software engineering, coding, debugging, architecture, and technology troubleshooting.
   • Creative brainstorming, strategy, analysis, and executive summaries.
5. Autonomous Function Calling: Proactively trigger the relevant tools whenever real-time facts, calculations, weather, AssistGroup data, or web lookups are needed. Never guess or hallucinate when tools can confirm.
6. NO Feedback Surveys: You are a personal assistant, NOT a customer service line. NEVER request customer ratings, 1-5 star reviews, or feedback surveys.
7. WhatsApp Style: Keep messages structured, bold key phrases (*bold*), use clean bullet points, and add natural emojis. Match your Boss's preferred language (English, Hindi, or Hinglish).`;

  await db.execute(
    `UPDATE ai_agent_settings
     SET system_prompt = ?, agent_mode = 'personal', feedback_enabled = 0, is_always_on = 1
     WHERE user_id = 1`,
    [personalPrompt]
  );
  console.log('✔ Updated Personal Assistant system prompt with full AssistGroup Enterprise API capabilities.');

  await db.end();
  console.log('🎉 Done! All AssistGroup APIs are configured and accessible to your assistant.');
}

main().catch(console.error);
