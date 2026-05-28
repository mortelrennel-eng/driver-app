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

// ─── OUR files to push ────────────────────────────────────────────────────────
// routes/web.php = merged version (web's new routes + our delete route restored)
// All other files = our new features (support unsend, FCM token fix, auth)
const filesToUpload = [
    // routes/web.php — merged: web's push notification & chat routes + our delete support route
    {
        local:  'routes/web.php',
        remote: `${BASE_REMOTE}/routes/web.php`
    },
    // api.php — our update: added delete message endpoint
    {
        local:  'routes/api.php',
        remote: `${BASE_REMOTE}/routes/api.php`
    },
    // Support Management Controller — our update: unsend/delete feature
    {
        local:  'app/Http/Controllers/SupportManagementController.php',
        remote: `${BASE_REMOTE}/app/Http/Controllers/SupportManagementController.php`
    },
    // Support view — our update: unsend UI + enter key send
    {
        local:  'resources/views/support/index.blade.php',
        remote: `${BASE_REMOTE}/resources/views/support/index.blade.php`
    },
    // API SupportController — our update: delete message endpoint
    {
        local:  'app/Http/Controllers/Api/SupportController.php',
        remote: `${BASE_REMOTE}/app/Http/Controllers/Api/SupportController.php`
    },
    // API AuthController — our update: FCM token fix
    {
        local:  'app/Http/Controllers/Api/AuthController.php',
        remote: `${BASE_REMOTE}/app/Http/Controllers/Api/AuthController.php`
    },
    // DriverAppController — our update: FCM token overlap fix
    {
        local:  'app/Http/Controllers/Api/DriverAppController.php',
        remote: `${BASE_REMOTE}/app/Http/Controllers/Api/DriverAppController.php`
    },
    // NotificationController — our update: FCM token overlap fix
    {
        local:  'app/Http/Controllers/Api/NotificationController.php',
        remote: `${BASE_REMOTE}/app/Http/Controllers/Api/NotificationController.php`
    },
    // AuthController — our update: FCM token clear on login
    {
        local:  'app/Http/Controllers/AuthController.php',
        remote: `${BASE_REMOTE}/app/Http/Controllers/AuthController.php`
    },
    // Migration — hidden columns for support messages
    {
        local:  'database/migrations/2026_05_26_152000_add_hidden_columns_to_support_messages.php',
        remote: `${BASE_REMOTE}/database/migrations/2026_05_26_152000_add_hidden_columns_to_support_messages.php`
    }
];

// Post-upload: just clear cache, migration was already run
const POST_COMMANDS = `cd ${BASE_REMOTE} && php artisan optimize:clear && php artisan config:clear && echo "---DONE---"`;

console.log('--- SFTP DEPLOY START ---');
console.log(`Uploading ${filesToUpload.length} files to Hostinger...`);
console.log('NOTE: routes/web.php = MERGED (web routes kept + our delete route restored)\n');

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
                console.log('\nClearing caches on server...');
                sftp.end();

                conn.exec(POST_COMMANDS, (err, stream) => {
                    if (err) {
                        console.error('Cache clear error:', err);
                        conn.end();
                        return;
                    }
                    stream.on('close', (code) => {
                        console.log(`\nCache clear completed (code: ${code})`);
                        conn.end();
                        if (code === 0) {
                            console.log('--- DEPLOYMENT COMPLETE! ---');
                        } else {
                            console.log('--- CACHE CLEAR MAY HAVE FAILED ---');
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
                console.error(`File not found: ${localPath}`);
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
