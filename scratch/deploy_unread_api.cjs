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
const BASE_LOCAL  = 'c:\\\\xampp\\\\htdocs\\\\eurotaxisystem-main';

const filesToUpload = [
    {
        local:  'app/Http/Controllers/LiveTrackingController.php',
        remote: `${BASE_REMOTE}/app/Http/Controllers/LiveTrackingController.php`
    },
    {
        local:  'app/Http/Controllers/Api/DriverAppController.php',
        remote: `${BASE_REMOTE}/app/Http/Controllers/Api/DriverAppController.php`
    }
];

function uploadFiles() {
    const conn = new Client();
    
    conn.on('ready', () => {
        console.log('SSH Client Ready');
        conn.sftp((err, sftp) => {
            if (err) {
                console.error('SFTP Error:', err);
                conn.end();
                return;
            }

            let index = 0;

            function next() {
                if (index >= filesToUpload.length) {
                    console.log('All files uploaded successfully.');
                    conn.end();
                    return;
                }

                const file = filesToUpload[index];
                const localPath = path.join(BASE_LOCAL, file.local);
                const remotePath = file.remote;

                console.log(`Uploading: ${localPath} -> ${remotePath}`);

                const readStream = fs.createReadStream(localPath);
                const writeStream = sftp.createWriteStream(remotePath);

                writeStream.on('close', () => {
                    console.log(`Successfully uploaded ${file.local}`);
                    index++;
                    next();
                });

                writeStream.on('error', (err) => {
                    console.error(`Error uploading ${file.local}:`, err);
                    conn.end();
                });

                readStream.pipe(writeStream);
            }

            next();
        });
    }).on('error', (err) => {
        console.error('SSH Connection Error:', err);
    }).connect(config);
}

uploadFiles();
