const Client = require('ssh2-sftp-client');
const config = { host: '195.35.62.133', port: 65002, username: 'u747826271', password: '@Admineuro2026', readyTimeout: 30000 };
const sftp = new Client();
sftp.connect(config)
    .then(() => sftp.delete('/home/u747826271/domains/eurotaxisystem.site/public_html/public/clear_opcache.php'))
    .then(() => { console.log('✅ Deleted clear_opcache.php from server'); return sftp.end(); })
    .catch(e => { console.error('❌ ' + e.message); sftp.end(); });
