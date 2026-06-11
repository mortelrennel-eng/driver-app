const { Client } = require('ssh2');
const config = { host: '195.35.62.133', port: 65002, username: 'u747826271', password: '@Admineuro2026', readyTimeout: 20000 };
const conn = new Client();
conn.on('ready', () => {
    conn.exec("grep -n 'Unban Driver\\|Archive\\|deleteDriver\\|driver_status.*banned' /home/u747826271/domains/eurotaxisystem.site/public_html/resources/views/driver-management/partials/_drivers_table.blade.php", (err, s) => {
        let o = '';
        s.on('data', d => o += d);
        s.stderr.on('data', d => o += 'ERR:' + d);
        s.on('close', () => {
            console.log('=== PRODUCTION BLADE VERIFICATION ===');
            console.log(o || 'NOTHING FOUND!');
            conn.end();
        });
    });
}).on('error', e => console.error('SSH Error:', e)).connect(config);
