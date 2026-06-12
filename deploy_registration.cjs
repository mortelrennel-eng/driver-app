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
const BASE_LOCAL  = __dirname;

const filesToUpload = [
    {
        local: 'app/Http/Controllers/Api/DriverAppController.php',
        remote: `${BASE_REMOTE}/app/Http/Controllers/Api/DriverAppController.php`
    },
    {
        local: 'driver-app-build.zip',
        remote: `${BASE_REMOTE}/driver-app-build.zip`
    }
];

const POST_COMMANDS = `
cd ${BASE_REMOTE} && 
mkdir -p public/driver-app && 
unzip -o driver-app-build.zip -d public/driver-app/ && 
rm driver-app-build.zip && 
php artisan optimize:clear && 
echo "---DONE---"
`;

console.log('--- SFTP DEPLOY START ---');

const conn = new Client();

conn.on('ready', () => {
    console.log('SSH Connection Ready. Starting SFTP...');
    conn.sftp((err, sftp) => {
        if (err) { console.error('SFTP Error:', err); conn.end(); return; }

        let uploadCount = 0;
        function uploadNext(index) {
            if (index >= filesToUpload.length) {
                console.log(`\nAll files uploaded! Extracting zip on server...`);
                sftp.end();
                conn.exec(POST_COMMANDS, (err, stream) => {
                    if (err) { console.error('Exec error:', err); conn.end(); return; }
                    stream.on('close', (code) => {
                        console.log(`\nServer commands completed (code: ${code})`);
                        console.log('--- DEPLOYMENT COMPLETE! ---');
                        conn.end();
                    }).on('data', d => process.stdout.write(`STDOUT: ${d}`))
                      .stderr.on('data', d => process.stderr.write(`STDERR: ${d}`));
                });
                return;
            }

            const file = filesToUpload[index];
            const localPath = path.join(BASE_LOCAL, file.local);
            const writeStream = sftp.createWriteStream(file.remote);

            writeStream.on('close', () => {
                uploadCount++;
                console.log(`✓ Uploaded ${file.local}`);
                uploadNext(index + 1);
            });

            writeStream.on('error', (err) => {
                console.error(`✗ Failed: ${file.local} — ${err.message}`);
                conn.end();
            });

            fs.createReadStream(localPath).pipe(writeStream);
        }
        uploadNext(0);
    });
}).on('error', (err) => {
    console.error('Connection Error:', err.message);
}).connect(config);
