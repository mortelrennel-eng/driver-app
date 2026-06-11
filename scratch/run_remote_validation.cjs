const { Client } = require('ssh2');

const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026',
    readyTimeout: 20000
};

const command = 'cd /home/u747826271/domains/eurotaxisystem.site/public_html && php scratch/profile_and_validate.php';

console.log('--- REMOTE PRODUCTION VALIDATION RUNNER ---');
console.log(`Connecting to ${config.host}:${config.port}...`);

const conn = new Client();

conn.on('ready', () => {
    console.log('SSH Connected. Executing remote validation script against production DB...');
    console.log('==================================================');

    conn.exec(command, (err, stream) => {
        if (err) {
            console.error('Execution Error:', err);
            conn.end();
            return;
        }

        stream.on('close', (code, signal) => {
            console.log('==================================================');
            console.log(`Stream Closed with code: ${code}`);
            conn.end();
        }).on('data', (data) => {
            process.stdout.write(data.toString());
        }).stderr.on('data', (data) => {
            process.stderr.write(data.toString());
        });
    });
}).on('error', (err) => {
    console.error('SSH Connection Error:', err);
}).connect(config);
