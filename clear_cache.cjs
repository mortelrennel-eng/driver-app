const { Client } = require('ssh2');
const conn = new Client();
conn.on('ready', () => {
    // First check the actual DB count
    const countCmd = 'mysql -u u747826271_eurotaxi -p@Eurotaxi123 u747826271_eurotaxi -e "SELECT COUNT(*) as total FROM units WHERE deleted_at IS NULL;"';
    conn.exec(countCmd, (err, stream) => {
        stream.on('data', data => console.log('DB COUNT: ' + data.toString()))
              .on('close', () => {
                  // Now clear all cache
                  conn.exec('cd /home/u747826271/domains/eurotaxisystem.site/public_html && php artisan cache:clear && php artisan config:clear && php artisan view:clear && php artisan route:clear && echo "ALL CACHE CLEARED"', (err2, stream2) => {
                      stream2.on('data', data => console.log('STDOUT: ' + data.toString()))
                             .on('close', () => conn.end());
                      stream2.stderr.on('data', data => console.log('ERR: ' + data));
                  });
              });
        stream.stderr.on('data', data => console.log('ERR: ' + data));
    });
}).connect({host: '195.35.62.133', port: 65002, username: 'u747826271', password: '@Admineuro2026'});
