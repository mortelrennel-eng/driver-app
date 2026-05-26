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
const BASE_LOCAL  = 'c:\\xampp\\htdocs\\eurotaxisystem-main';

const filesToUpload = [
    { local: 'app/Http/Controllers/Api/SupportController.php', remote: `${BASE_REMOTE}/app/Http/Controllers/Api/SupportController.php` },
    { local: 'app/Http/Controllers/SupportManagementController.php', remote: `${BASE_REMOTE}/app/Http/Controllers/SupportManagementController.php` },
    { local: 'app/Models/SupportMessage.php', remote: `${BASE_REMOTE}/app/Models/SupportMessage.php` },
    { local: 'app/Models/SupportTicket.php', remote: `${BASE_REMOTE}/app/Models/SupportTicket.php` },
    { local: 'resources/views/support/index.blade.php', remote: `${BASE_REMOTE}/resources/views/support/index.blade.php` },
    { local: 'resources/views/layouts/app.blade.php', remote: `${BASE_REMOTE}/resources/views/layouts/app.blade.php` },
    { local: 'routes/web.php', remote: `${BASE_REMOTE}/routes/web.php` },
    { local: 'routes/api.php', remote: `${BASE_REMOTE}/routes/api.php` },
    { local: 'database/migrations/2026_05_08_204852_create_support_tickets_table.php', remote: `${BASE_REMOTE}/database/migrations/2026_05_08_204852_create_support_tickets_table.php` },
    { local: 'database/migrations/2026_05_09_140227_create_support_messages_table.php', remote: `${BASE_REMOTE}/database/migrations/2026_05_09_140227_create_support_messages_table.php` },
    { local: 'app/Services/NotificationService.php', remote: `${BASE_REMOTE}/app/Services/NotificationService.php` }
];

const POST_COMMANDS = `cd ${BASE_REMOTE} && php artisan migrate --force && mkdir -p uploads/support_attachments && chmod 775 uploads/support_attachments && php artisan view:clear && php artisan optimize:clear && echo "---DEPLOY_SUCCESS---"`;

console.log('--- SUPPORT SYSTEM DEPLOY START ---');
console.log(`Uploading ${filesToUpload.length} files to Hostinger...`);

const conn = new Client();

conn.on('ready', () => {
    console.log('SSH Connection Ready. Starting SFTP...');

    conn.sftp((err, sftp) => {
        if (err) {
            console.error('SFTP Error:', err);
            conn.end();
            return;
        }

        let uploadCount = 0;

        function uploadNext(index) {
            if (index >= filesToUpload.length) {
                console.log(`\nAll ${uploadCount} files uploaded successfully!`);
                console.log('\nCreating folders and clearing caches on server...');
                sftp.end();

                conn.exec(POST_COMMANDS, (err, stream) => {
                    if (err) {
                        console.error('Post command error:', err);
                        conn.end();
                        return;
                    }
                    stream.on('close', (code) => {
                        console.log(`\nPost commands completed (code: ${code})`);
                        conn.end();
                        if (code === 0) {
                            console.log('--- DEPLOYMENT COMPLETE! ---');
                        }
                    }).on('data', (data) => {
                        process.stdout.write(`STDOUT: ${data}`);
                    }).stderr.on('data', (data) => {
                        process.stderr.write(`STDERR: ${data}`);
                    });
                });
                return;
            }

            const file = filesToUpload[index];
            const localPath = path.join(BASE_LOCAL, file.local);

            if (!fs.existsSync(localPath)) {
                // Warning, maybe file isn't needed or doesn't exist
                console.warn(`File not found, skipping: ${localPath}`);
                uploadNext(index + 1);
                return;
            }

            const localContent = fs.readFileSync(localPath);
            const writeStream = sftp.createWriteStream(file.remote);

            writeStream.on('close', () => {
                uploadCount++;
                console.log(`✓ [${uploadCount}/${filesToUpload.length}] ${file.local}`);
                uploadNext(index + 1);
            });

            writeStream.on('error', (err) => {
                console.error(`✗ Failed: ${file.local} — ${err.message}`);
                uploadNext(index + 1);
            });

            writeStream.end(localContent);
        }

        uploadNext(0);
    });

}).on('error', (err) => {
    console.error('Connection Error:', err.message);
}).connect(config);
