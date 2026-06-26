const fs = require('fs');
const Client = require('ssh2-sftp-client');
const sftp = new Client();
const config = {
  host: '195.35.62.133',
  port: 65002,
  username: 'u747826271',
  password: '@Admineuro2026',
  readyTimeout: 20000
};

async function upload() {
  try {
    await sftp.connect(config);
    console.log('Connected to SFTP');
    await sftp.fastPut('routes/api.php', '/home/u747826271/domains/eurotaxisystem.site/public_html/routes/api.php');
    console.log('Upload of api.php complete');
  } catch (err) {
    console.error(err);
  } finally {
    sftp.end();
  }
}

upload();
