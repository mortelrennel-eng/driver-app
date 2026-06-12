const { Client } = require('ssh2');
const fs = require('fs');
const path = require('path');

const config = { host: '195.35.62.133', port: 65002, username: 'u747826271', password: '@Admineuro2026', readyTimeout: 30000 };
const BASE_REMOTE = '/home/u747826271/domains/eurotaxisystem.site/public_html/public/driver-app';
const BASE_LOCAL = path.join(__dirname, 'public/driver-app');

function getFiles(dir, filesList = []) {
    const files = fs.readdirSync(dir);
    for (const file of files) {
        const fullPath = path.join(dir, file);
        if (fs.statSync(fullPath).isDirectory()) {
            getFiles(fullPath, filesList);
        } else {
            filesList.push(fullPath);
        }
    }
    return filesList;
}

const allFiles = getFiles(BASE_LOCAL);
const conn = new Client();

conn.on('ready', () => {
    console.log('SSH Ready. Uploading public/driver-app/ ...');
    conn.sftp((err, sftp) => {
        if (err) throw err;

        // Ensure remote directories exist
        const dirs = [...new Set(allFiles.map(f => path.dirname(f).replace(BASE_LOCAL, '').replace(/\\/g, '/')))].filter(d => d);
        
        let dirIndex = 0;
        function makeNextDir() {
            if (dirIndex >= dirs.length) return startUpload();
            const remoteDir = BASE_REMOTE + dirs[dirIndex];
            sftp.mkdir(remoteDir, err => {
                // Ignore error (dir probably exists)
                dirIndex++;
                makeNextDir();
            });
        }

        makeNextDir();

        let fileIndex = 0;
        function startUpload() {
            if (fileIndex >= allFiles.length) {
                console.log('All files uploaded successfully!');
                sftp.end();
                conn.exec('cd /home/u747826271/domains/eurotaxisystem.site/public_html && php artisan optimize:clear', (err, stream) => {
                    stream.on('close', () => {
                        console.log('Cache cleared. Done!');
                        conn.end();
                    });
                });
                return;
            }

            const localPath = allFiles[fileIndex];
            const remotePath = BASE_REMOTE + localPath.replace(BASE_LOCAL, '').replace(/\\/g, '/');
            
            sftp.fastPut(localPath, remotePath, err => {
                if (err) console.error('Failed to upload', remotePath, err);
                fileIndex++;
                startUpload();
            });
        }
    });
}).on('error', (err) => {
    console.error('SSH Connection Error (ignored):', err.message);
}).connect(config);
