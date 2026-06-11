const { Client } = require('ssh2');
const fs = require('fs');
const path = require('path');

const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026',
    readyTimeout: 30000
};

const BASE_REMOTE = '/home/u747826271/domains/eurotaxisystem.site/public_html';
const BASE_LOCAL  = path.resolve(__dirname, '..');

const filesToUpload = [
    {
        local:  'app/Providers/AppServiceProvider.php',
        remote: `${BASE_REMOTE}/app/Providers/AppServiceProvider.php`
    },
    {
        local:  'app/Models/Boundary.php',
        remote: `${BASE_REMOTE}/app/Models/Boundary.php`
    },
    {
        local:  'app/Models/Expense.php',
        remote: `${BASE_REMOTE}/app/Models/Expense.php`
    },
    {
        local:  'app/Models/Maintenance.php',
        remote: `${BASE_REMOTE}/app/Models/Maintenance.php`
    },
    {
        local:  'app/Models/Unit.php',
        remote: `${BASE_REMOTE}/app/Models/Unit.php`
    },
    {
        local:  'app/Models/User.php',
        remote: `${BASE_REMOTE}/app/Models/User.php`
    },
    {
        local:  'routes/api.php',
        remote: `${BASE_REMOTE}/routes/api.php`
    },
    {
        local:  'app/Http/Controllers/Api/NotificationController.php',
        remote: `${BASE_REMOTE}/app/Http/Controllers/Api/NotificationController.php`
    },
    {
        local:  'app/Services/FirebasePushService.php',
        remote: `${BASE_REMOTE}/app/Services/FirebasePushService.php`
    },
    {
        local:  'storage/app/firebase/firebase-credentials.json',
        remote: `${BASE_REMOTE}/storage/app/firebase/firebase-credentials.json`
    },
    {
        local:  'database/migrations/2026_05_06_170000_add_fcm_token_to_users_table.php',
        remote: `${BASE_REMOTE}/database/migrations/2026_05_06_170000_add_fcm_token_to_users_table.php`
    },
    {
        local:  'resources/views/layouts/app.blade.php',
        remote: `${BASE_REMOTE}/resources/views/layouts/app.blade.php`
    },
    {
        local:  'resources/views/my-account/index.blade.php',
        remote: `${BASE_REMOTE}/resources/views/my-account/index.blade.php`
    },
    {
        local:  'routes/web.php',
        remote: `${BASE_REMOTE}/routes/web.php`
    },
    {
        local:  'app/Http/Controllers/AuthController.php',
        remote: `${BASE_REMOTE}/app/Http/Controllers/AuthController.php`
    },
    {
        local:  'app/Services/NotificationService.php',
        remote: `${BASE_REMOTE}/app/Services/NotificationService.php`
    },
    {
        local:  'resources/js/app.js',
        remote: `${BASE_REMOTE}/resources/js/app.js`
    },
    {
        local:  'public/assets/app.js',
        remote: `${BASE_REMOTE}/public/assets/app.js`
    }
];

const POST_COMMANDS = [
    `cd ${BASE_REMOTE}`,
    'php artisan migrate --force',
    'php artisan view:clear',
    'php artisan optimize:clear',
    'echo "---DEPLOY_AND_MIGRATE_COMPLETE---"'
].join(' && ');

console.log('--- SFTP DEPLOYING CACHING & FCM UPDATES ---');
console.log(`Uploading ${filesToUpload.length} files to Hostinger...`);

const conn = new Client();

conn.on('ready', () => {
    console.log('SSH Connection Ready. Creating remote directory structure...');

    // Pre-create any directories to avoid SFTP upload failure
    conn.exec(`mkdir -p ${BASE_REMOTE}/storage/app/firebase ${BASE_REMOTE}/app/Services`, (err, stream) => {
        if (err) {
            console.error('Directory creation failed:', err);
            conn.end();
            return;
        }
        stream.on('close', () => {
            console.log('Remote directories successfully verified. Initializing SFTP upload...');
            
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
                        console.log('\nRunning migrations and clearing cache on remote server...');
                        sftp.end();

                        // Run migration and cache clear commands
                        conn.exec(POST_COMMANDS, (err, stream) => {
                            if (err) {
                                console.error('Execution error:', err);
                                conn.end();
                                return;
                            }
                            stream.on('close', (code) => {
                                console.log(`\nServer execution completed (code: ${code})`);
                                conn.end();
                                if (code === 0) {
                                    console.log('--- DEPLOYMENT & MIGRATION SUCCESSFUL! ---');
                                } else {
                                    console.log('--- SERVER COMMANDS FINISHED WITH WARNINGS ---');
                                }
                            }).on('data', (data) => {
                                process.stdout.write(data);
                            }).stderr.on('data', (data) => {
                                process.stderr.write(data);
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
        }).on('data', d => process.stdout.write(d)).stderr.on('data', d => process.stderr.write(d));
    });
}).on('error', (err) => {
    console.error('Connection Error:', err.message);
}).connect(config);
