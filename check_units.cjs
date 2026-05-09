const { Client } = require('ssh2');
const conn = new Client();
conn.on('ready', () => {
    const cmd = 'mysql -u u747826271_eurotaxi -p@Eurotaxi123 u747826271_eurotaxi -e "SELECT COUNT(*) as total_units FROM units WHERE deleted_at IS NULL;"';
    conn.exec(cmd, (err, stream) => {
        stream.on('data', data => console.log('STDOUT: ' + data.toString()))
              .on('close', () => conn.end());
        stream.stderr.on('data', data => console.log('ERR: ' + data));
    });
}).connect({host: '195.35.62.133', port: 65002, username: 'u747826271', password: '@Admineuro2026'});
