const { Client } = require('ssh2');

const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026',
    readyTimeout: 30000
};

const BASE_REMOTE = '/home/u747826271/domains/eurotaxisystem.site/public_html';

const conn = new Client();

conn.on('ready', () => {
    console.log('SSH Connection Ready. Testing OAuth2 Google Token Generation and FCM handshake on Hostinger...');

    // We will call the FirebasePushService directly with a dummy token to verify the handshake/auth success
    conn.exec(`cd ${BASE_REMOTE} && php artisan tinker --execute="print_r(App\\Services\\FirebasePushService::sendPush('Test Handshake', 'Testing OAuth2 Connection', 'dummy_token_123', 'test_type'))"`, (err, stream) => {
        if (err) {
            console.error('Execution failed:', err);
            conn.end();
            return;
        }
        stream.on('close', (code) => {
            conn.end();
        }).on('data', (data) => {
            process.stdout.write(data);
        }).stderr.on('data', (data) => {
            process.stderr.write(data);
        });
    });
}).on('error', (err) => {
    console.error('Connection Error:', err.message);
}).connect(config);
