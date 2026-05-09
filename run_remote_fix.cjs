const { Client } = require('ssh2');
const fs = require('fs');

const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026',
    readyTimeout: 20000
};

const conn = new Client();
conn.on('ready', () => {
    console.log('SSH Connection Ready.');
    conn.sftp((err, sftp) => {
        if (err) throw err;
        const readStream = fs.createReadStream('db_fix.php');
        const writeStream = sftp.createWriteStream('/home/u747826271/domains/eurotaxisystem.site/public_html/db_fix.php');
        
        writeStream.on('close', () => {
            console.log('File uploaded. Executing...');
            conn.exec('cd /home/u747826271/domains/eurotaxisystem.site/public_html && php db_fix.php && rm db_fix.php', (err, stream) => {
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
