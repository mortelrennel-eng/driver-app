const { Client } = require('ssh2');
const fs = require('fs');

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
const localFile = 'c:\\xampp\\htdocs\\eurotaxisystem-main\\scratch\\check_unit_data.php';
const remoteFile = '/home/u747826271/domains/eurotaxisystem.site/public_html/scratch_check_unit.php';

conn.on('ready', () => {
    console.log('SSH Connected. Uploading check script...');
    conn.sftp((err, sftp) => {
        if (err) throw err;
        const readStream = fs.createReadStream(localFile);
        const writeStream = sftp.createWriteStream(remoteFile);
        writeStream.on('close', () => {
            console.log('Script uploaded. Executing...');
            conn.exec(`php ${remoteFile}`, (err, stream) => {
                if (err) throw err;
                stream.on('close', () => {
                    conn.end();
                }).on('data', (data) => {
                    process.stdout.write(data);
                }).stderr.on('data', (data) => {
                    process.stderr.write(data);
                });
            });
        });
        readStream.pipe(writeStream);
    });
}).connect(config);
