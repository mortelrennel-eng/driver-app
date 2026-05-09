const { Client } = require('ssh2');
const fs = require('fs');

const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026'
};

const conn = new Client();

console.log('Connecting to Hostinger...');
conn.on('ready', () => {
    console.log('SSH Connection Ready. Uploading users_dump.sql...');
    
    conn.sftp((err, sftp) => {
        if (err) throw err;
        
        const readStream = fs.createReadStream('full_db_clean.sql');
        const writeStream = sftp.createWriteStream('/home/u747826271/domains/eurotaxisystem.site/full_db_clean.sql');
        
        writeStream.on('close', () => {
            console.log('Upload complete. Importing into database...');
            
            const importCmd = 'cd /home/u747826271/domains/eurotaxisystem.site && (echo "SET FOREIGN_KEY_CHECKS = 0;" && cat full_db_clean.sql && echo "SET FOREIGN_KEY_CHECKS = 1;") | mysql -u u747826271_eurotaxi -p@Eurotaxi123 u747826271_eurotaxi && rm full_db_clean.sql';
            
            conn.exec(importCmd, (err, stream) => {
                if (err) throw err;
                
                stream.on('close', (code) => {
                    if (code === 0) {
                        console.log('Database import successful!');
                    } else {
                        console.log('Database import failed with code: ' + code);
                    }
                    conn.end();
                }).on('data', (data) => {
                    console.log('STDOUT: ' + data);
                }).stderr.on('data', (data) => {
                    console.log('STDERR: ' + data);
                });
            });
        });
        
        readStream.pipe(writeStream);
    });
}).on('error', (err) => {
    console.error('Connection Error:', err);
}).connect(config);
