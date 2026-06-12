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
    { local: 'app/Http/Controllers/UnitController.php', remote: '/home/u747826271/domains/eurotaxisystem.site/public_html/app/Http/Controllers/UnitController.php' }
];

const POST_COMMANDS = `cd ${BASE_REMOTE} && php artisan optimize:clear && php artisan config:clear && php artisan route:clear && php artisan view:clear && echo "---DONE---"`;

console.log('--- TRACKING FIX DEPLOY ---');
console.log(`Uploading ${filesToUpload.length} backend files to Hostinger...\n`);

const conn = new Client();

conn.on('ready', () => {
    console.log('SSH Ready. Starting SFTP...');
    conn.sftp((err, sftp) => {
        if (err) { console.error('SFTP Error:', err); conn.end(); return; }

        let uploadCount = 0;
        function uploadNext(index) {
            if (index >= filesToUpload.length) {
                console.log(`\nAll ${uploadCount} files uploaded!`);
                console.log('\nClearing server caches...');
                sftp.end();

                conn.exec(POST_COMMANDS, (err, stream) => {
                    if (err) { console.error('Exec error:', err); conn.end(); return; }
                    stream.on('close', (code) => {
                        console.log(`\nCache clear done (code: ${code})`);
                        conn.end();
                        console.log('--- DEPLOY COMPLETE! ---');
                    }).on('data', (data) => {
                        process.stdout.write(`${data}`);
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
