const { Client } = require('ssh2');
const conn = new Client();

const command = `php /home/u747826271/domains/eurotaxisystem.site/public_html/artisan tinker --execute="echo json_encode(DB::table('maintenance')->select('status', DB::raw('count(*) as count'), DB::raw('CASE WHEN deleted_at IS NULL THEN 0 ELSE 1 END as is_deleted'))->groupBy('status', 'is_deleted')->get());"`;

conn.on('ready', () => {
    conn.exec(command, (err, stream) => {
        if (err) throw err;
        stream.on('close', () => conn.end()).on('data', (data) => console.log('DATA: ' + data)).stderr.on('data', (data) => console.log('ERR: ' + data));
    });
}).connect({ host: '195.35.62.133', port: 65002, username: 'u747826271', password: '@Admineuro2026' });
