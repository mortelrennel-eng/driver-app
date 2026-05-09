const { Client } = require('ssh2');
const mysql = require('mysql2/promise');

async function main() {
  // Use direct MySQL connection (not SSH + shell) to avoid $ escaping issues
  const conn = await mysql.createConnection({
    host: 'srv483.hstgr.io',
    user: 'u747826271_eurotaxi',
    password: '@Eurotaxi123',
    database: 'u747826271_eurotaxi',
    port: 3306
  });
  
  console.log('MySQL connected!');
  
  // Generate proper bcrypt hash using Node.js bcrypt
  const bcrypt = require('bcryptjs');
  const newPassword = 'Admin@2026';
  const hash = await bcrypt.hash(newPassword, 10);
  console.log('Generated hash:', hash.substring(0, 25) + '...');
  console.log('Is bcrypt:', hash.startsWith('$2'));
  
  // Update the password_hash column with the proper bcrypt hash
  const [result] = await conn.execute(
    "UPDATE users SET password_hash = ?, password = ?, must_change_password = 0 WHERE email = 'robertgarcia.owner@gmail.com'",
    [hash, hash]
  );
  
  console.log('Rows updated:', result.affectedRows);
  
  // Verify the hash was stored correctly
  const [rows] = await conn.execute(
    "SELECT LEFT(password_hash, 25) as hash_prefix FROM users WHERE email = 'robertgarcia.owner@gmail.com'"
  );
  console.log('Stored hash prefix:', rows[0]?.hash_prefix);
  
  // Verify password works
  const verify = await bcrypt.compare(newPassword, hash);
  console.log('Password verifies:', verify);
  console.log('\nCREDENTIALS:');
  console.log('Email: robertgarcia.owner@gmail.com');
  console.log('Password: Admin@2026');
  
  await conn.end();
}

main().catch(e => {
  console.error('Error:', e.message);
  process.exit(1);
});
