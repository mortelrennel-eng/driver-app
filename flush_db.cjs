const { Client } = require('ssh2');
const mysql = require('mysql2/promise');

async function main() {
  try {
    const conn = await mysql.createConnection({
      host: 'srv483.hstgr.io',
      user: 'u747826271_eurotaxi',
      password: '@Eurotaxi123',
      database: 'u747826271_eurotaxi',
      port: 3306
    });
    
    console.log('MySQL connected!');
    const [result] = await conn.execute("FLUSH USER_RESOURCES;");
    console.log('FLUSH SUCCESS:', result);
    await conn.end();
  } catch (e) {
    console.error('Error:', e.message);
  }
}

main();
