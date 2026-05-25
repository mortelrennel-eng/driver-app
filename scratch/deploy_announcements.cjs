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
        local:  'app/Models/Announcement.php',
        remote: `${BASE_REMOTE}/app/Models/Announcement.php`
    },
    {
        local:  'app/Http/Controllers/AnnouncementController.php',
        remote: `${BASE_REMOTE}/app/Http/Controllers/AnnouncementController.php`
    },
    {
        local:  'app/Http/Controllers/Api/AnnouncementController.php',
        remote: `${BASE_REMOTE}/app/Http/Controllers/Api/AnnouncementController.php`
    },
    {
        local:  'resources/views/announcements/index.blade.php',
        remote: `${BASE_REMOTE}/resources/views/announcements/index.blade.php`
    },
    {
        local:  'routes/web.php',
        remote: `${BASE_REMOTE}/routes/web.php`
    },
    {
        local:  'routes/api.php',
        remote: `${BASE_REMOTE}/routes/api.php`
    },
    {
        local:  'app/Services/NotificationService.php',
        remote: `${BASE_REMOTE}/app/Services/NotificationService.php`
    },
    {
        local:  'resources/views/layouts/app.blade.php',
        remote: `${BASE_REMOTE}/resources/views/layouts/app.blade.php`
    },
    {
        local:  'app/Http/Controllers/SuperAdminController.php',
        remote: `${BASE_REMOTE}/app/Http/Controllers/SuperAdminController.php`
    },
    {
        local:  'database/migrations/2026_05_14_222800_create_announcements_table.php',
        remote: `${BASE_REMOTE}/database/migrations/2026_05_14_222800_create_announcements_table.php`
    },
    {
        local:  'database/migrations/2026_05_18_171226_update_announcements_table.php',
        remote: `${BASE_REMOTE}/database/migrations/2026_05_18_171226_update_announcements_table.php`
    },
    {
        local:  'database/migrations/2026_05_20_152855_add_title_to_announcements_table.php',
        remote: `${BASE_REMOTE}/database/migrations/2026_05_20_152855_add_title_to_announcements_table.php`
    }
];

const POST_COMMANDS = [
    `cd ${BASE_REMOTE}`,
    'php artisan migrate --force',
    'php artisan view:clear',
    'php artisan optimize:clear',
    'echo "---DEPLOY_AND_MIGRATE_COMPLETE---"'
].join(' && ');

console.log('🚀 --- DEPLOYING ANNOUNCEMENT SYSTEM TO HOSTINGER ---');
console.log(`📦 Uploading ${filesToUpload.length} files...`);

const conn = new Client();

conn.on('ready', () => {
    console.log('✅ SSH Connection Ready.');

    // Pre-create any directories to avoid SFTP upload failure
    conn.exec(`mkdir -p ${BASE_REMOTE}/resources/views/announcements`, (err, stream) => {
        if (err) {
            console.error('❌ Directory creation failed:', err);
            conn.end();
            return;
        }
        stream.on('close', () => {
            console.log('📂 Remote directories verified.');
            
            conn.sftp((err, sftp) => {
                if (err) {
                    console.error('❌ SFTP Error:', err);
                    conn.end();
                    return;
                }

                let uploadCount = 0;

                function uploadNext(index) {
                    if (index >= filesToUpload.length) {
                        console.log(`\n🎉 All ${uploadCount} files uploaded successfully!`);
                        console.log('\n⚙️ Running migrations and clearing cache on remote server...');
                        sftp.end();

                        // Run migration and cache clear commands
                        conn.exec(POST_COMMANDS, (err, stream) => {
                            if (err) {
                                console.error('❌ Execution error:', err);
                                conn.end();
                                return;
                            }
                            stream.on('close', (code) => {
                                console.log(`\n💻 Server execution completed (code: ${code})`);
                                conn.end();
                                if (code === 0) {
                                    console.log('✅ --- DEPLOYMENT & MIGRATION SUCCESSFUL! ---');
                                } else {
                                    console.log('⚠️ --- SERVER COMMANDS FINISHED WITH WARNINGS ---');
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
                        console.error(`❌ File not found: ${localPath}`);
                        uploadNext(index + 1);
                        return;
                    }

                    const localContent = fs.readFileSync(localPath);
                    const writeStream = sftp.createWriteStream(file.remote);

                    writeStream.on('close', () => {
                        uploadCount++;
                        console.log(`   ✓ [${uploadCount}/${filesToUpload.length}] ${file.local}`);
                        uploadNext(index + 1);
                    });

                    writeStream.on('error', (err) => {
                        console.error(`   ✗ Failed: ${file.local} — ${err.message}`);
                        uploadNext(index + 1);
                    });

                    writeStream.end(localContent);
                }

                uploadNext(0);
            });
        }).on('data', d => process.stdout.write(d)).stderr.on('data', d => process.stderr.write(d));
    });
}).on('error', (err) => {
    console.error('❌ Connection Error:', err.message);
}).connect(config);
