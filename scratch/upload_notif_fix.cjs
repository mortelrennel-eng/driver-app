const { Client } = require('ssh2');
const fs = require('fs');

const config = { host: '195.35.62.133', port: 65002, username: 'u747826271', password: '@Admineuro2026', readyTimeout: 30000 };
const BASE_REMOTE = '/home/u747826271/domains/eurotaxisystem.site/public_html';

const conn = new Client();
conn.on('ready', () => {
  conn.sftp((err, sftp) => {
    if (err) { console.error(err); conn.end(); return; }
    
    const content = fs.readFileSync('app/Services/NotificationService.php');
    const ws = sftp.createWriteStream(BASE_REMOTE + '/app/Services/NotificationService.php');
    
    ws.on('close', () => { 
      console.log('? Uploaded NotificationService.php');
      sftp.end();
      conn.exec('cd ' + BASE_REMOTE + ' && php artisan cache:clear', (err, stream) => {
        stream.on('close', () => {
            console.log('Cache cleared.');
            conn.end();
        });
      });
    });
    ws.end(content);
  });
}).connect(config);
