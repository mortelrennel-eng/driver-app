const { Client } = require('ssh2');
const conn = new Client();

const command = `php /home/u747826271/domains/eurotaxisystem.site/public_html/artisan tinker --execute="echo 'Total Drivers from Query: ' . DB::table('drivers as d')->leftJoin('units as unit', function(\\$join) { \\$join->on('d.id', '=', 'unit.driver_id')->orOn('d.id', '=', 'unit.secondary_driver_id'); })->whereNull('d.deleted_at')->whereNull('unit.deleted_at')->distinct('d.id')->count('d.id') . '\n';"`;

conn.on('ready', () => {
    conn.exec(command, (err, stream) => {
        if (err) throw err;
        stream.on('close', () => conn.end()).on('data', (data) => console.log('DATA: ' + data)).stderr.on('data', (data) => console.log('ERR: ' + data));
    });
}).connect({ host: '195.35.62.133', port: 65002, username: 'u747826271', password: '@Admineuro2026' });
