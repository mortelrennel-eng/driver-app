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
        local:  'resources/views/partials/chat-drawer.blade.php',
        remote: `${BASE_REMOTE}/resources/views/partials/chat-drawer.blade.php`
    }
];

const POST_COMMANDS = [
    `cd ${BASE_REMOTE}`,
    'php artisan view:clear',
    'echo "---DEPLOY_COMPLETE---"'
].join(' && ');

console.log('--- SFTP DEPLOYING CHAT DRAWER FIX ---');
console.log(`Uploading ${filesToUpload.length} files to Hostinger...`);

const conn = new Client();

conn.on('ready', () => {
    console.log('SSH Connection Ready. Initializing SFTP upload...');
    
    conn.sftp((err, sftp) => {
        if (err) {
            console.error('SFTP Error:', err);
            conn.end();
            return;
        }

        let uploadCount = 0;

        function uploadNext(index) {
            if (index >= filesToUpload.length) {
                sftp.end();

                // Run cache clear commands
                conn.exec(POST_COMMANDS, (err, stream) => {
                    if (err) {
                        conn.end();
                        return;
                    }
                    stream.on('close', (code) => {
                        conn.end();
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

            // Ensure remote directory exists (partials)
            conn.exec(`mkdir -p ${BASE_REMOTE}/resources/views/partials`, (err, stream) => {
                stream.on('close', () => {
                    const localContent = fs.readFileSync(localPath);
                    const writeStream = sftp.createWriteStream(file.remote);

                    writeStream.on('close', () => {
                        uploadCount++;
                        uploadNext(index + 1);
                    });

                    writeStream.end(localContent);
                });
            });
        }

        uploadNext(0);
    });
}).on('error', (err) => {
    console.error('Connection Error:', err.message);
}).connect(config);
