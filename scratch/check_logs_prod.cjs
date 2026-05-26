const { Client } = require('ssh2'); 
const conn = new Client(); 
conn.on('ready', () => { 
    conn.exec('tail -n 100 /home/u747826271/domains/eurotaxisystem.site/public_html/storage/logs/laravel.log', (err, stream) => { 
        stream.on('data', data => console.log(data.toString())).on('close', () => conn.end()); 
    }); 
}).connect({host: '195.35.62.133', port: 65002, username: 'u747826271', password: '@Admineuro2026'});
