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
const BASE_LOCAL = path.resolve(__dirname, '..');

// All files related to: Notification, Announcement, Staff/Account Deletion, Support
const filesToDownload = [
    // --- NOTIFICATION ---
    'app/Services/NotificationService.php',
    'app/Http/Controllers/Api/NotificationController.php',
    'app/Providers/AppServiceProvider.php',
    'app/Models/Maintenance.php',
    'app/Http/Controllers/AuthController.php',

    // --- ANNOUNCEMENT ---
    'app/Http/Controllers/AnnouncementController.php',
    'app/Models/Announcement.php',
    'resources/views/announcements/index.blade.php',

    // --- STAFF / ACCOUNT DELETION ---
    'app/Http/Controllers/StaffController.php',
    'app/Http/Controllers/Api/DriverAppController.php',

    // --- SUPPORT (already pushed but pull latest to be safe) ---
    'app/Http/Controllers/SupportController.php',

    // --- ROUTES (master file) ---
    'routes/web.php',
    'routes/api.php',
];

console.log('--- SFTP PULL FEATURE FILES START ---');
console.log(`Pulling ${filesToDownload.length} feature files from Hostinger...`);

const conn = new Client();

conn.on('ready', () => {
    console.log('SSH Connection Ready. Starting SFTP...');
    conn.sftp((err, sftp) => {
        if (err) {
            console.error('SFTP Error:', err);
            conn.end();
            return;
        }

        let downloadCount = 0;

        function downloadNext(index) {
            if (index >= filesToDownload.length) {
                console.log(`\nAll ${downloadCount} files downloaded successfully!`);
                sftp.end();
                conn.end();
                return;
            }

            const file = filesToDownload[index];
            const remotePath = `${BASE_REMOTE}/${file}`;
            const localPath = path.join(BASE_LOCAL, file);
            const localDir = path.dirname(localPath);

            if (!fs.existsSync(localDir)) {
                fs.mkdirSync(localDir, { recursive: true });
            }

            sftp.fastGet(remotePath, localPath, (err) => {
                if (err) {
                    console.error(`✗ Failed: ${file} — ${err.message}`);
                } else {
                    downloadCount++;
                    console.log(`✓ [${downloadCount}/${filesToDownload.length}] ${file}`);
                }
                downloadNext(index + 1);
            });
        }

        downloadNext(0);
    });
}).on('error', (err) => {
    console.error('Connection Error:', err.message);
}).connect(config);
