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

const filesToUpload = [
    {
        local:  'routes/api.php',
        remote: `${BASE_REMOTE}/routes/api.php`
    },
    {
        local:  'routes/web.php',
        remote: `${BASE_REMOTE}/routes/web.php`
    },
    {
        local:  'app/Http/Controllers/Api/DriverAppController.php',
        remote: `${BASE_REMOTE}/app/Http/Controllers/Api/DriverAppController.php`
    },
    {
        local:  'app/Http/Controllers/SuperAdminController.php',
        remote: `${BASE_REMOTE}/app/Http/Controllers/SuperAdminController.php`
    },
    {
        local:  'app/Http/Controllers/ArchiveController.php',
        remote: `${BASE_REMOTE}/app/Http/Controllers/ArchiveController.php`
    },
    {
        local:  'resources/views/archive/index.blade.php',
        remote: `${BASE_REMOTE}/resources/views/archive/index.blade.php`
    },
    {
        local:  'resources/views/archive/partials/_user_accounts_table.blade.php',
        remote: `${BASE_REMOTE}/resources/views/archive/partials/_user_accounts_table.blade.php`
    },
    {
        local:  'resources/views/layouts/app.blade.php',
        remote: `${BASE_REMOTE}/resources/views/layouts/app.blade.php`
    },
    {
        local:  'app/Http/Controllers/Api/AuthController.php',
        remote: `${BASE_REMOTE}/app/Http/Controllers/Api/AuthController.php`
    },
    {
        local:  'app/helpers.php',
        remote: `${BASE_REMOTE}/app/helpers.php`
    },
    {
        local:  'app/Helpers/SemaphoreHelper.php',
        remote: `${BASE_REMOTE}/app/Helpers/SemaphoreHelper.php`
    },
    {
        local:  'resources/views/support/index.blade.php',
        remote: `${BASE_REMOTE}/resources/views/support/index.blade.php`
    },
    {
        local:  'app/Http/Controllers/AuthController.php',
        remote: `${BASE_REMOTE}/app/Http/Controllers/AuthController.php`
    },
];

// Post-upload commands to clear caches
const POST_COMMANDS = `cd ${BASE_REMOTE} && php artisan view:clear && php artisan optimize:clear && echo "---CACHE_CLEARED---"`;

console.log('--- SFTP DEPLOY START ---');
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
                console.log('\nClearing caches on server...');
                sftp.end();

                // Run cache clear commands
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
