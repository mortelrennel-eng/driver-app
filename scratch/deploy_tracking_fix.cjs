const { Client } = require('ssh2');
const fs = require('fs');

const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026',
};

const BASE_REMOTE = '/home/u747826271/domains/eurotaxisystem.site/public_html';
const BASE_LOCAL = 'c:\\xampp\\htdocs\\eurotaxisystem-main';

const filesToUpload = [
    'app/Http/Controllers/Api/DriverAppController.php'
];

const conn = new Client();

conn.on('ready', () => {
    console.log('SSH Connection Ready. Starting SFTP...');
    conn.sftp((err, sftp) => {
        if (err) throw err;
        let uploadCount = 0;
        function uploadNext() {
            if (uploadCount >= filesToUpload.length) {
                console.log('Deployment Complete!');
                conn.end();
                return;
            }
            const relPath = filesToUpload[uploadCount];
            const localPath = `${BASE_LOCAL}\\${relPath.replace(/\//g, '\\')}`;
            const remotePath = `${BASE_REMOTE}/${relPath}`;
            
            sftp.fastPut(localPath, remotePath, (err) => {
                if (err) console.error('Error uploading:', relPath, err);
                else console.log(`✓ Uploaded: ${relPath}`);
                uploadCount++;
                uploadNext();
            });
        }
        uploadNext();
    });
}).connect(config);
