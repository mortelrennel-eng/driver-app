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

// ─── Files to upload for the Banned/Suspended Driver Feature ─────────────────
const filesToUpload = [
    // Controller — has suspendOrBan(), unban(), banned() methods
    {
        local:  'app/Http/Controllers/DriverManagementController.php',
        remote: `${BASE_REMOTE}/app/Http/Controllers/DriverManagementController.php`
    },
    // Routes — has /driver-management/banned, /suspend-or-ban, /unban routes
    {
        local:  'routes/web.php',
        remote: `${BASE_REMOTE}/routes/web.php`
    },
    // Banned drivers page view
    {
        local:  'resources/views/driver-management/banned.blade.php',
        remote: `${BASE_REMOTE}/resources/views/driver-management/banned.blade.php`
    },
    // Driver management index (has suspend/ban modal + JS logic)
    {
        local:  'resources/views/driver-management/index.blade.php',
        remote: `${BASE_REMOTE}/resources/views/driver-management/index.blade.php`
    },
    // Drivers table partial (has dropdown with Suspend/Ban or Restore buttons)
    {
        local:  'resources/views/driver-management/partials/_drivers_table.blade.php',
        remote: `${BASE_REMOTE}/resources/views/driver-management/partials/_drivers_table.blade.php`
    },
    // Driver details modal partial
    {
        local:  'resources/views/driver-management/partials/_driver_details_modal.blade.php',
        remote: `${BASE_REMOTE}/resources/views/driver-management/partials/_driver_details_modal.blade.php`
    },
    // App layout — has sidebar "Banned Drivers" sub-menu item
    {
        local:  'resources/views/layouts/app.blade.php',
        remote: `${BASE_REMOTE}/resources/views/layouts/app.blade.php`
    },
];

// Post-upload: clear all caches + run migration for suspended_until/suspension_reason columns
const POST_COMMANDS = [
    `cd ${BASE_REMOTE}`,
    `php artisan config:clear`,
    `php artisan route:clear`,
    `php artisan view:clear`,
    `php artisan cache:clear`,
    `php artisan migrate --force`,
    `echo "---DEPLOY COMPLETE---"`
].join(' && ');

console.log('');
console.log('╔══════════════════════════════════════════════════════════╗');
console.log('║   DEPLOY: Driver Banned/Suspended Feature → Hostinger   ║');
console.log('╚══════════════════════════════════════════════════════════╝');
console.log(`\nUploading ${filesToUpload.length} files to live server...`);
console.log(`Target: ${BASE_REMOTE}\n`);

const conn = new Client();

conn.on('ready', () => {
    console.log('✓ SSH Connected to Hostinger\n');

    conn.sftp((err, sftp) => {
        if (err) {
            console.error('✗ SFTP Error:', err);
            conn.end();
            return;
        }

        let uploadCount = 0;
        let failCount = 0;

        function uploadNext(index) {
            if (index >= filesToUpload.length) {
                console.log(`\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━`);
                console.log(`✓ Upload complete: ${uploadCount} files uploaded, ${failCount} failed`);
                console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n');
                console.log('Running post-deploy commands (cache clear + migrate)...\n');
                sftp.end();

                conn.exec(POST_COMMANDS, (err, stream) => {
                    if (err) {
                        console.error('✗ Post-deploy commands failed:', err);
                        conn.end();
                        return;
                    }
                    stream.on('close', (code) => {
                        console.log(`\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━`);
                        if (code === 0) {
                            console.log('✓ ALL DONE! Live server is now updated.');
                            console.log('  → https://eurotaxisystem.site/driver-management/banned');
                        } else {
                            console.log('⚠ Post-deploy finished with code:', code);
                        }
                        console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n');
                        conn.end();
                    }).on('data', (data) => {
                        process.stdout.write(`  ${data}`);
                    }).stderr.on('data', (data) => {
                        process.stderr.write(`  [STDERR] ${data}`);
                    });
                });
                return;
            }

            const file = filesToUpload[index];
            const localPath = path.join(BASE_LOCAL, file.local);

            if (!fs.existsSync(localPath)) {
                console.error(`✗ File not found locally: ${file.local}`);
                failCount++;
                uploadNext(index + 1);
                return;
            }

            const localContent = fs.readFileSync(localPath);
            const writeStream = sftp.createWriteStream(file.remote);

            writeStream.on('close', () => {
                uploadCount++;
                console.log(`  ✓ [${uploadCount}/${filesToUpload.length}] ${file.local}`);
                uploadNext(index + 1);
            });

            writeStream.on('error', (err) => {
                failCount++;
                console.error(`  ✗ Failed: ${file.local} — ${err.message}`);
                uploadNext(index + 1);
            });

            writeStream.end(localContent);
        }

        uploadNext(0);
    });

}).on('error', (err) => {
    console.error('✗ SSH Connection Error:', err.message);
    console.error('  Check: host, port, credentials in config');
}).connect(config);
