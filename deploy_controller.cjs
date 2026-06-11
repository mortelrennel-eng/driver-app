const fs = require('fs');
const { Client } = require('ssh2');

const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026',
    readyTimeout: 30000
};

const localPath = 'c:/xampp/htdocs/eurotaxisystem/app/Http/Controllers/LiveTrackingController.php';
const remotePath = '/home/u747826271/domains/eurotaxisystem.site/public_html/app/Http/Controllers/LiveTrackingController.php';

const conn = new Client();
conn.on('ready', () => {
    console.log('SSH connection established');
    conn.sftp((err, sftp) => {
        if (err) throw err;
        
        console.log('SFTP connected, reading local file...');
        const readStream = fs.createReadStream(localPath);
        const writeStream = sftp.createWriteStream(remotePath);
        
        writeStream.on('close', () => {
            console.log('LiveTrackingController.php successfully transferred to Hostinger!');
            conn.end();
        });
        
        writeStream.on('error', (err) => {
            console.error('Error writing file:', err);
            conn.end();
        });
        
        readStream.pipe(writeStream);
    });
}).on('error', (err) => {
    console.error('SSH connection error:', err);
}).connect(config);
