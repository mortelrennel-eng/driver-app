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
const BACKUP_DIR = path.join(__dirname, 'notification_backup');

const filesToPull = [
    'app/Services/NotificationService.php',
    'app/Models/Maintenance.php',
    'app/Providers/AppServiceProvider.php'
];

if (!fs.existsSync(BACKUP_DIR)) {
    fs.mkdirSync(BACKUP_DIR, { recursive: true });
}

console.log('--- SFTP PULL NOTIFICATION FILES START ---');
console.log(`Pulling ${filesToPull.length} files from Hostinger to ${BACKUP_DIR}...`);

const conn = new Client();

conn.on('ready', () => {
    console.log('SSH Connection Ready. Starting SFTP...');

    conn.sftp((err, sftp) => {
        if (err) {
            console.error('SFTP Error:', err);
            conn.end();
            return;
        }

        let pullCount = 0;

        function pullNext(index) {
            if (index >= filesToPull.length) {
                console.log(`\nAll ${pullCount} files pulled successfully!`);
                sftp.end();
                conn.end();
                return;
            }

            const file = filesToPull[index];
            const remotePath = `${BASE_REMOTE}/${file}`;
            const localPath = path.join(BACKUP_DIR, file);

            // Ensure local directory exists
            const localDir = path.dirname(localPath);
            if (!fs.existsSync(localDir)) {
                fs.mkdirSync(localDir, { recursive: true });
            }

            sftp.fastGet(remotePath, localPath, (err) => {
                if (err) {
                    console.error(`✗ Failed to pull ${file}: ${err.message}`);
                } else {
                    pullCount++;
                    console.log(`✓ Pulled: ${file}`);
                }
                pullNext(index + 1);
            });
        }

        pullNext(0);
    });

}).on('error', (err) => {
    console.error('Connection Error:', err.message);
}).connect(config);
