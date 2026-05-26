const { Client } = require('ssh2');
const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026',
    readyTimeout: 30000,
    algorithms: {
        kex: ['diffie-hellman-group14-sha256', 'diffie-hellman-group14-sha1', 'diffie-hellman-group1-sha1'],
        cipher: ['aes128-ctr', 'aes192-ctr', 'aes256-ctr', 'aes128-gcm', 'aes256-gcm'],
        serverHostKey: ['ssh-rsa', 'ecdsa-sha2-nistp256'],
        hmac: ['hmac-sha2-256', 'hmac-sha1']
    }
};

const PHP_TEST = `
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\\Contracts\\Console\\Kernel::class);
$kernel->bootstrap();

try {
    $msg = \\App\\Models\\SupportMessage::create([
        'driver_id' => 1, // assuming driver 1 exists
        'sender_type' => 'admin',
        'sender_id' => 1,
        'message' => 'test message from CLI',
    ]);
    echo "Message created ID: " . $msg->id . "\\n";
} catch (\\Exception $e) {
    echo "Error: " . $e->getMessage() . "\\n";
}
`;

const conn = new Client();
conn.on('ready', () => {
    conn.exec(`cat << 'EOF' > /home/u747826271/domains/eurotaxisystem.site/public_html/test_db.php\n${PHP_TEST}\nEOF\nphp /home/u747826271/domains/eurotaxisystem.site/public_html/test_db.php`, (err, stream) => {
        if (err) throw err;
        stream.on('close', () => { conn.end(); })
              .on('data', (data) => { console.log(data.toString()); })
              .stderr.on('data', (data) => { console.error(data.toString()); });
    });
}).connect(config);
