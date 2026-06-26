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

const BASE_REMOTE = '/home/u747826271/domains/eurotaxisystem.site/public_html/public/driver-app';
const BASE_LOCAL  = path.join(__dirname, 'driver-app', 'dist');

const conn = new Client();

async function uploadFile(sftp, localFile, remoteFile) {
    return new Promise((resolve, reject) => {
        const normalizedRemote = remoteFile.replace(/\\/g, '/');
        sftp.fastPut(localFile, normalizedRemote, (err) => {
            if (err) reject(err);
            else resolve();
        });
    });
}

async function uploadDirRecursive(sftp, localPath, remotePath) {
    const stats = fs.statSync(localPath);
    const normalizedRemote = remotePath.replace(/\\/g, '/');
    
    if (stats.isDirectory()) {
        await new Promise((resolve) => {
            sftp.mkdir(normalizedRemote, (err) => {
                resolve(); 
            });
        });

        const files = fs.readdirSync(localPath);
        for (const file of files) {
            await uploadDirRecursive(sftp, path.join(localPath, file), path.join(remotePath, file));
        }
    } else {
        await uploadFile(sftp, localPath, remotePath);
    }
}

conn.on('ready', () => {
    console.log('Syncing driver-app dist files to production...');
    conn.sftp(async (err, sftp) => {
        if (err) {
            console.error('SFTP Error:', err);
            conn.end();
            return;
        }

        try {
            console.log('Ensuring remote public/driver-app exists...');
            await new Promise((resolve) => {
                sftp.mkdir(BASE_REMOTE, (err) => {
                    resolve(); 
                });
            });

            const files = fs.readdirSync(BASE_LOCAL);
            console.log(`Found ${files.length} items in local dist folder.`);
            for (const file of files) {
                console.log(`-> Syncing: ${file}`);
                await uploadDirRecursive(sftp, path.join(BASE_LOCAL, file), path.join(BASE_REMOTE, file));
            }
            
            console.log('App deployment complete.');
            conn.end();
        } catch (e) {
            console.error('Deployment failed:', e);
            conn.end();
        }
    });
}).connect(config);
