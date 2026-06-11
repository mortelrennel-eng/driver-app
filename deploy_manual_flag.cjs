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
        serverHostKey: ['ssh-ed25519', 'ssh-rsa', 'rsa-sha2-512', 'rsa-sha2-256']
    }
};

const REMOTE_DIR = '/home/u747826271/domains/eurotaxisystem.site/public_html';
const LOCAL_DIR = __dirname;

const filesToUpload = [
    'app/Http/Controllers/UnitController.php',
    'routes/web.php',
    'resources/views/units/flagged.blade.php'
];

const conn = new Client();

conn.on('ready', () => {
    console.log('✓ SSH Connected');
    conn.sftp((err, sftp) => {
        if (err) throw err;
        
        let uploadsCompleted = 0;
        
        filesToUpload.forEach((relPath, index) => {
            const localPath = path.join(LOCAL_DIR, relPath);
            const remotePath = `${REMOTE_DIR}/${relPath}`.replace(/\\/g, '/');
            
            sftp.fastPut(localPath, remotePath, (err) => {
                if (err) {
                    console.error(`✗ Failed: ${relPath} - ${err.message}`);
                } else {
                    console.log(`  ✓ [${index + 1}/${filesToUpload.length}] ${relPath}`);
                }
                
                uploadsCompleted++;
                if (uploadsCompleted === filesToUpload.length) {
                    console.log('\n✓ All files uploaded\nClearing caches on server...\n');
                    
                    conn.exec(`cd ${REMOTE_DIR} && /opt/alt/php82/usr/bin/php artisan view:clear && /opt/alt/php82/usr/bin/php artisan route:clear`, (err, stream) => {
                        if (err) throw err;
                        stream.on('close', (code, signal) => {
                            console.log('---DONE---');
                            console.log('\n✓ DEPLOY COMPLETE! Live server updated.');
                            conn.end();
                        }).on('data', (data) => {
                            console.log(data.toString());
                        }).stderr.on('data', (data) => {
                            console.error(data.toString());
                        });
                    });
                }
            });
        });
    });
}).connect(config);
