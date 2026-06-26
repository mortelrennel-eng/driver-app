const { Client } = require('ssh2');

const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026'
};

const conn = new Client();

console.log('Connecting to Hostinger via SSH...');
conn.on('ready', () => {
    console.log('SSH Connection Ready. Running migration...');
    
    const migrateCmd = 'cd /home/u747826271/domains/eurotaxisystem.site/public_html && php artisan migrate --force';
    
    conn.exec(migrateCmd, (err, stream) => {
        if (err) throw err;
        
        stream.on('close', (code, signal) => {
            console.log('Migration process closed with code ' + code);
            conn.end();
        }).on('data', (data) => {
            console.log('STDOUT: ' + data);
        }).stderr.on('data', (data) => {
            console.log('STDERR: ' + data);
        });
    });
}).on('error', (err) => {
    console.error('Connection Error:', err);
}).connect(config);
