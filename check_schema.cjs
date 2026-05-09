const { Client } = require('ssh2');
const conn = new Client();
conn.on('ready', () => {
    conn.exec('mysql -u u747826271_eurotaxi -p@Eurotaxi123 u747826271_eurotaxi -e "DESC system_alerts;"', (err, stream) => {
        stream.on('data', data => console.log('SCHEMA:\n' + data.toString()))
              .on('close', () => conn.end());
        stream.stderr.on('data', data => console.log('ERR: ' + data));
    });
}).connect({host: '195.35.62.133', port: 65002, username: 'u747826271', password: '@Admineuro2026'});
