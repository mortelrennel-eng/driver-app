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
    await sftp.fastPut('app/Http/Controllers/Api/DriverAppController.php', '/home/u747826271/domains/eurotaxisystem.site/public_html/app/Http/Controllers/Api/DriverAppController.php');
    await sftp.fastPut('app/Http/Controllers/LiveTrackingController.php', '/home/u747826271/domains/eurotaxisystem.site/public_html/app/Http/Controllers/LiveTrackingController.php');
    await sftp.fastPut('app/Services/TracksolidService.php', '/home/u747826271/domains/eurotaxisystem.site/public_html/app/Services/TracksolidService.php');
    await sftp.fastPut('public/js/realtime-tracking.js', '/home/u747826271/domains/eurotaxisystem.site/public_html/public/js/realtime-tracking.js');
    console.log('Upload complete');
  } catch (err) {
    console.error(err);
  } finally {
    sftp.end();
  }
}

upload();
