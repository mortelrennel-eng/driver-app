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

const conn = new Client();
conn.on('ready', () => {
    console.log('SSH Ready');
    conn.sftp((err, sftp) => {
        if (err) throw err;
        const localPath = 'c:\\xampp\\htdocs\\eurotaxisystem-main\\.env_production';
        const remotePath = '/home/u747826271/domains/eurotaxisystem.site/public_html/.env';
        const writeStream = sftp.createWriteStream(remotePath);
        writeStream.on('close', () => {
            console.log('Successfully uploaded .env_production to remote .env');
            sftp.end();
            conn.exec('cd /home/u747826271/domains/eurotaxisystem.site/public_html && php artisan config:clear && php artisan optimize:clear', (err, stream) => {
                if (err) throw err;
                stream.on('close', () => {
                    console.log('Caches cleared on server');
                    conn.end();
                    process.exit(0);
                }).on('data', d => process.stdout.write(d)).stderr.on('data', d => process.stderr.write(d));
            });
        });
        writeStream.end(fs.readFileSync(localPath));
    });
}).connect(config);
