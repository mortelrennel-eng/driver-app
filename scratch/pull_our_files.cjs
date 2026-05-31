const { Client } = require('ssh2');
const fs = require('fs');
const path = require('path');

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

const BASE_REMOTE = '/home/u747826271/domains/eurotaxisystem.site/public_html';
const BASE_LOCAL  = __dirname;
const BACKUP_DIR  = path.join(BASE_LOCAL, 'scratch', 'hostinger_backup_' + Date.now());

const filesToPull = [
    'app/Http/Controllers/SupportManagementController.php',
    'resources/views/support/index.blade.php',
    'app/Http/Controllers/Api/SupportController.php',
    'app/Http/Controllers/Api/AuthController.php',
    'database/migrations/2026_05_26_152000_add_hidden_columns_to_support_messages.php',
    'routes/api.php',
    'routes/web.php',
    'app/Http/Controllers/Api/DriverAppController.php',
    'app/Http/Controllers/Api/NotificationController.php',
    'app/Http/Controllers/AuthController.php'
];

console.log('--- SFTP PULL (read-only) ---');
console.log(`Pulling ${filesToPull.length} files from Hostinger to: scratch/hostinger_backup_*\n`);

const conn = new Client();

conn.on('ready', () => {
    conn.sftp((err, sftp) => {
        if (err) { console.error('SFTP Error:', err); conn.end(); return; }

        let count = 0;

        function pullNext(index) {
            if (index >= filesToPull.length) {
                console.log(`\nAll ${count} files pulled successfully!`);
                console.log(`Saved to: ${BACKUP_DIR}`);
                sftp.end();
                conn.end();
                return;
            }

            const file = filesToPull[index];
            const remotePath = `${BASE_REMOTE}/${file}`;
            const localPath  = path.join(BACKUP_DIR, file);
            const localDir   = path.dirname(localPath);

            if (!fs.existsSync(localDir)) fs.mkdirSync(localDir, { recursive: true });

            sftp.fastGet(remotePath, localPath, (err) => {
                if (err) {
                    console.error(`✗ Failed: ${file} — ${err.message}`);
                } else {
                    count++;
                    console.log(`✓ [${count}/${filesToPull.length}] ${file}`);
                }
                pullNext(index + 1);
            });
        }

        pullNext(0);
    });
}).on('error', (err) => {
    console.error('Connection Error:', err.message);
}).connect(config);
