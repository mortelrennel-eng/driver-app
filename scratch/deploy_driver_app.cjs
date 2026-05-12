const { Client } = require('ssh2');
const fs = require('fs');
const path = require('path');

const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026',
};

const BASE_REMOTE = '/home/u747826271/domains/eurotaxisystem.site/public_html/driver-app';
const BASE_LOCAL  = 'c:\\xampp\\htdocs\\eurotaxisystem-main\\driver-app\\dist';

const conn = new Client();

function getAllFiles(dirPath, arrayOfFiles) {
    const files = fs.readdirSync(dirPath);
    arrayOfFiles = arrayOfFiles || [];

    files.forEach(function(file) {
        if (fs.statSync(dirPath + "/" + file).isDirectory()) {
            arrayOfFiles = getAllFiles(dirPath + "/" + file, arrayOfFiles);
        } else {
            arrayOfFiles.push(path.join(dirPath, "/", file));
        }
    });

    return arrayOfFiles;
}

conn.on('ready', () => {
    console.log('SSH Connection Ready. Starting SFTP for Driver App...');

    conn.sftp((err, sftp) => {
        if (err) throw err;

        const files = getAllFiles(BASE_LOCAL);
        console.log(`Uploading ${files.length} files to ${BASE_REMOTE}...`);

        let uploadCount = 0;

        function uploadFile(index) {
            if (index >= files.length) {
                console.log('\nDeployment Complete!');
                conn.end();
                return;
            }

            const localPath = files[index];
            const relativePath = path.relative(BASE_LOCAL, localPath).replace(/\\/g, '/');
            const remotePath = `${BASE_REMOTE}/${relativePath}`;

            // Ensure remote directory exists (simple version - assumes 1 level deep for assets)
            const remoteDir = path.dirname(remotePath);
            sftp.mkdir(remoteDir, (err) => {
                // Ignore error if directory already exists
                const readStream = fs.createReadStream(localPath);
                const writeStream = sftp.createWriteStream(remotePath);

                writeStream.on('close', () => {
                    uploadCount++;
                    process.stdout.write(`\rProgress: ${uploadCount}/${files.length} files uploaded.`);
                    uploadFile(index + 1);
                });

                writeStream.on('error', (err) => {
                    console.error(`\nError uploading ${localPath}:`, err);
                    uploadFile(index + 1);
                });

                readStream.pipe(writeStream);
            });
        }

        uploadFile(0);
    });
}).connect(config);
