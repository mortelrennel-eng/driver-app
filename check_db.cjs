const mysql = require('mysql2/promise');

async function test() {
    const conn = await mysql.createConnection({
        host: '195.35.62.133',
        user: 'u747826271_eurotaxi',
        password: '@Eurotaxi123',
        database: 'u747826271_eurotaxi'
    });
    
    const [rows] = await conn.execute('SELECT plate_number, imei FROM units WHERE plate_number = ?', ['AAA 4591']);
    console.log('AAA 4591 DB IMEI:', rows[0].imei);
    
    const [all] = await conn.execute('SELECT count(*) as c FROM units WHERE imei IS NOT NULL AND imei != ""');
    console.log('Total units with IMEI:', all[0].c);
    
    conn.end();
}
test().catch(console.error);
