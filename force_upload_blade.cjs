const { Client } = require('ssh2');
const fs = require('fs');
const path = require('path');

const config = { host: '195.35.62.133', port: 65002, username: 'u747826271', password: '@Admineuro2026', readyTimeout: 20000 };

const localFile = 'c:\\xampp\\htdocs\\eurotaxisystem\\resources\\views\\driver-management\\partials\\_drivers_table.blade.php';
const fileContent = fs.readFileSync(localFile);
console.log(`Uploading ${fileContent.length} bytes...`);

const conn = new Client();
conn.on('ready', () => {
    conn.sftp((err, sftp) => {
        if (err) { console.error('SFTP error:', err); conn.end(); return; }
        const remotePath = '/home/u747826271/domains/eurotaxisystem.site/public_html/resources/views/driver-management/partials/_drivers_table.blade.php';
        const ws = sftp.createWriteStream(remotePath);
        ws.on('close', () => {
            console.log('✅ Uploaded!');
            conn.exec('cd /home/u747826271/domains/eurotaxisystem.site/public_html && php artisan view:clear', (e, s) => {
                let o = '';
                s.on('data', d => o += d);
                s.on('close', () => { console.log('View clear:', o.trim()); conn.end(); });
            });
        });
        ws.on('error', e => { console.error('Write error:', e); conn.end(); });
        ws.write(fileContent);
        ws.end();
    });
}).on('error', e => console.error('SSH Error:', e)).connect(config);
