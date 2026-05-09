const { Client } = require('ssh2');
const conn = new Client();

const command = `php /home/u747826271/domains/eurotaxisystem.site/public_html/artisan tinker --execute="echo json_encode(DB::table('maintenance')->where('status', 'cancelled')->count());"`;

conn.on('ready', () => {
    conn.exec(command, (err, stream) => {
        if (err) throw err;
        stream.on('close', () => conn.end()).on('data', (data) => console.log('DATA: ' + data)).stderr.on('data', (data) => console.log('ERR: ' + data));
    });
}).connect({ host: '195.35.62.133', port: 65002, username: 'u747826271', password: '@Admineuro2026' });
