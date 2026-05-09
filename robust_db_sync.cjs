const { Client } = require('ssh2');
const fs = require('fs');

const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026'
};

const conn = new Client();

console.log('Connecting to Hostinger for Robust Data Sync...');
conn.on('ready', () => {
    console.log('SSH Connection Ready. Uploading data dump...');
    
    conn.sftp((err, sftp) => {
        if (err) throw err;
        
        const readStream = fs.createReadStream('hostinger_data.sql');
        const writeStream = sftp.createWriteStream('/home/u747826271/domains/eurotaxisystem.site/hostinger_data.sql');
        
        writeStream.on('close', () => {
            console.log('Upload complete. Wiping DB, Migrating, and Importing Data...');
            
            const commands = [
                'cd /home/u747826271/domains/eurotaxisystem.site/public_html',
                'php artisan db:wipe --force',
                'php artisan migrate --force',
                '(echo "SET FOREIGN_KEY_CHECKS = 0;" && cat ../hostinger_data.sql && echo "SET FOREIGN_KEY_CHECKS = 1;") | mysql -u u747826271_eurotaxi -p@Eurotaxi123 u747826271_eurotaxi',
                'rm ../hostinger_data.sql'
            ].join(' && ');
            
            conn.exec(commands, (err, stream) => {
                if (err) throw err;
                
                stream.on('close', (code) => {
                    if (code === 0) {
                        console.log('Database synced successfully!');
                    } else {
                        console.log('Sync failed with code: ' + code);
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
