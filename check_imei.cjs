const { Client } = require('ssh2');
const config = { host: '195.35.62.133', port: 65002, username: 'u747826271', password: '@Admineuro2026', readyTimeout: 30000 };

const conn = new Client();
conn.on('ready', () => {
    // Direct MySQL query - bypass Laravel bootstrap
    const mysqlCmd = `mysql -u u747826271_eurotaxi -p'EuroTaxi@2024' u747826271_eurotaxi -e "SELECT plate_number, imei, status FROM units ORDER BY plate_number;" 2>/dev/null`;
    conn.exec(mysqlCmd, (err, s) => {
        let o = '';
        s.on('data', d => o += d);
        s.stderr.on('data', d => o += 'ERR:' + d);
        s.on('close', () => {
            console.log('=== UNITS TABLE (plate_number, imei, status) ===');
            console.log(o || '(empty result)');
            
            // Also check .env for Tracksolid credentials
            conn.exec("grep 'TRACKSOLID\\|DB_' /home/u747826271/domains/eurotaxisystem.site/public_html/.env", (err2, s2) => {
                let o2 = '';
                s2.on('data', d => o2 += d);
                s2.stderr.on('data', d => o2 += d);
                s2.on('close', () => {
                    console.log('\n=== .ENV TRACKSOLID CONFIG ===');
                    console.log(o2);
                    conn.end();
                });
            });
        });
    });
}).on('error', e => console.error('SSH Error:', e)).connect(config);
