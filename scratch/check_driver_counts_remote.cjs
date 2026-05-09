const { Client } = require('ssh2');
const conn = new Client();

const command = `php /home/u747826271/domains/eurotaxisystem.site/public_html/artisan tinker --execute="echo 'Drivers (Not Deleted): ' . DB::table('drivers')->whereNull('deleted_at')->count() . '\n'; echo 'Users with role driver (Not Deleted): ' . DB::table('users')->where('role', 'driver')->whereNull('deleted_at')->count() . '\n'; echo 'Drivers without Users: ' . DB::table('drivers')->whereNotExists(function(\\$q) { \\$q->select(DB::raw(1))->from('users')->whereRaw('users.id = drivers.user_id'); })->count() . '\n'; echo 'Users role driver without Driver record: ' . DB::table('users')->where('role', 'driver')->whereNotExists(function(\\$q) { \\$q->select(DB::raw(1))->from('drivers')->whereRaw('drivers.user_id = users.id'); })->count() . '\n';"`;

conn.on('ready', () => {
    conn.exec(command, (err, stream) => {
        if (err) throw err;
        stream.on('close', () => conn.end()).on('data', (data) => console.log('DATA: ' + data)).stderr.on('data', (data) => console.log('ERR: ' + data));
    });
}).connect({ host: '195.35.62.133', port: 65002, username: 'u747826271', password: '@Admineuro2026' });
