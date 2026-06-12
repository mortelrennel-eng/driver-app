const fs = require('fs');
const Client = require('ssh2-sftp-client');

const sftp = new Client();
const config = {
  host: '212.1.208.199',
  port: 22,
  username: 'u747826271',
  password: 'Password123!'
};

async function upload() {
  try {
    await sftp.connect(config);
    console.log('Connected to SFTP');
    await sftp.fastPut('public/test_api.php', '/home/u747826271/domains/eurotaxisystem.site/public_html/public/test_api.php');
    console.log('Uploaded test_api.php');
  } catch (err) {
    console.error(err);
  } finally {
    sftp.end();
  }
}
upload();
