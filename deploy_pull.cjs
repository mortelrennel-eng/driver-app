const { Client } = require('ssh2');

const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026',
    readyTimeout: 30000
};

const command = `cd /home/u747826271/domains/eurotaxisystem.site/public_html && git pull origin main 2>&1 && php artisan config:cache 2>&1 && php artisan route:cache 2>&1 && echo "---DEPLOY_SUCCESS---"`;

console.log('--- DEPLOYING TO PRODUCTION ---');

const conn = new Client();

conn.on('ready', () => {
    console.log('SSH Connected. Running git pull...');
    conn.exec(command, (err, stream) => {
        if (err) { console.error('Exec Error:', err); conn.end(); return; }
        stream.on('close', (code) => {
            console.log(`\nDone. Exit code: ${code}`);
            conn.end();
        }).on('data', (data) => {
            process.stdout.write(`${data}`);
        }).stderr.on('data', (data) => {
            process.stderr.write(`STDERR: ${data}`);
        });
    });
}).on('error', (err) => {
    console.error('Connection Error:', err);
}).connect(config);
