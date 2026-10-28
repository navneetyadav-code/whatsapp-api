import mysql from '../worker/node_modules/mysql2/promise.js';

async function main() {
  console.log('=====================================================');
  console.log(' CREATING ASSISTGROUP ADMIN API TABLES               ');
  console.log('=====================================================');

  const db = await mysql.createConnection({
    host: '127.0.0.1',
    user: 'root',
    password: '',
    database: 'assistgroup'
  });

  // 1. admin_api_tokens
  await db.execute(`
    CREATE TABLE IF NOT EXISTS admin_api_tokens (
      id INT(11) NOT NULL AUTO_INCREMENT,
      admin_id INT(11) NOT NULL,
      name VARCHAR(100) NOT NULL DEFAULT 'Default API Token',
      token_prefix VARCHAR(16) NOT NULL,
      token_hash VARCHAR(64) NOT NULL,
      scopes JSON NOT NULL,
      ip_whitelist JSON NULL DEFAULT NULL,
      last_used_at DATETIME NULL DEFAULT NULL,
      last_used_ip VARCHAR(45) NULL DEFAULT NULL,
      expires_at DATETIME NULL DEFAULT NULL,
      is_revoked TINYINT(1) NOT NULL DEFAULT 0,
      created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (id),
      UNIQUE KEY idx_token_hash (token_hash),
      KEY idx_admin_id (admin_id),
      KEY idx_token_prefix (token_prefix)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
  `);
  console.log('✔ Table created/verified: admin_api_tokens');

  // 2. api_rate_limits
  await db.execute(`
    CREATE TABLE IF NOT EXISTS api_rate_limits (
      id INT(11) NOT NULL AUTO_INCREMENT,
      identifier VARCHAR(128) NOT NULL,
      endpoint VARCHAR(128) NOT NULL,
      hits INT(11) NOT NULL DEFAULT 1,
      window_start INT(11) NOT NULL,
      PRIMARY KEY (id),
      UNIQUE KEY idx_identifier_endpoint (identifier, endpoint),
      KEY idx_window_start (window_start)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
  `);
  console.log('✔ Table created/verified: api_rate_limits');

  await db.end();
  console.log('🎉 Database tables initialized successfully!');
}

main().catch(err => {
  console.error('❌ Error creating tables:', err);
  process.exit(1);
});
