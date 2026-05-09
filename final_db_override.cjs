const { Client } = require('ssh2');
const fs = require('fs');

const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026'
};

const conn = new Client();

console.log('Connecting to Hostinger for Final DB Override...');
conn.on('ready', () => {
    console.log('SSH Connection Ready. Uploading full_db_clean.sql...');
    
    conn.sftp((err, sftp) => {
        if (err) throw err;
        
        const readStream = fs.createReadStream('full_db_clean.sql');
        const writeStream = sftp.createWriteStream('/home/u747826271/domains/eurotaxisystem.site/full_db_clean.sql');
        
        writeStream.on('close', () => {
            console.log('Upload complete. Wiping DB and Importing raw SQL...');
            
            const commands = [
                'cd /home/u747826271/domains/eurotaxisystem.site/public_html',
                'php artisan db:wipe --force',
                'mysql -u u747826271_eurotaxi -p@Eurotaxi123 u747826271_eurotaxi < ../full_db_clean.sql',
                'rm ../full_db_clean.sql'
            ].join(' && ');
            
            conn.exec(commands, (err, stream) => {
                if (err) throw err;
                
                stream.on('close', (code) => {
                    if (code === 0) {
                        console.log('Database override successful!');
                    } else {
                        console.log('Override failed with code: ' + code);
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
