const Client = require('ssh2-sftp-client');
const config = { host: '195.35.62.133', port: 65002, username: 'u747826271', password: '@Admineuro2026', readyTimeout: 30000 };
const sftp = new Client();
const localFile = 'public/clear_opcache.php';
const remoteFile = '/home/u747826271/domains/eurotaxisystem.site/public_html/public/clear_opcache.php';
sftp.connect(config)
    .then(() => sftp.fastPut(localFile, remoteFile))
    .then(() => { console.log('✅ Uploaded clear_opcache.php'); return sftp.end(); })
    .catch(e => { console.error('❌ ' + e.message); sftp.end(); });
