const { Client } = require('ssh2');
const fs = require('fs');
const path = require('path');

const config = {
    host: 'eurotaxisystem.site',
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

// ─── Latest changes: IMEI fix, Kill Engine UI, AkshGps robust response ─────
const filesToUpload = [
    // Service: AkshGpsService — improved engine command response detection
    {
        local:  'app/Services/AkshGpsService.php',
        remote: `${BASE_REMOTE}/app/Services/AkshGpsService.php`
    },
    // JS: realtime-tracking — "Already Kill Engine" / "Already Restored" UI feedback
    {
        local:  'public/js/realtime-tracking.js',
        remote: `${BASE_REMOTE}/public/js/realtime-tracking.js`
    },
    // View: units index — IMEI input accepts 10+ digits (AKSH GPS uses 11)
    {
        local:  'resources/views/units/index.blade.php',
        remote: `${BASE_REMOTE}/resources/views/units/index.blade.php`
    },
    // Controller: LiveTrackingController — Engine status persistent save
    {
        local:  'app/Http/Controllers/LiveTrackingController.php',
        remote: `${BASE_REMOTE}/app/Http/Controllers/LiveTrackingController.php`
    },
    // Controller: UnitController - Backend validation logic fix for IMEI (10-30 characters)
    {
        local:  'app/Http/Controllers/UnitController.php',
        remote: `${BASE_REMOTE}/app/Http/Controllers/UnitController.php`
    },
    // Migrations: GPS provider and engine status
    {
        local:  'database/migrations/2026_06_05_164800_add_gps_provider_to_units_table.php',
        remote: `${BASE_REMOTE}/database/migrations/2026_06_05_164800_add_gps_provider_to_units_table.php`
    },
    {
        local:  'database/migrations/2026_06_05_205523_add_engine_status_to_units_table.php',
        remote: `${BASE_REMOTE}/database/migrations/2026_06_05_205523_add_engine_status_to_units_table.php`
    }
];

// Post-upload: clear caches
const POST_COMMANDS = `cd ${BASE_REMOTE} && php artisan migrate --force && php artisan optimize:clear && php artisan config:clear && echo "---DONE---"`;

console.log('--- SFTP DEPLOY START ---');
console.log(`Uploading ${filesToUpload.length} files to Hostinger...`);
console.log('NOTE: Deploying IMEI fix + Kill Engine UI + AkshGps improvements\n');

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
