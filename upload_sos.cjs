const fs = require('fs');
const Client = require('ssh2-sftp-client');

const sftp = new Client();
const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026'
};

const basePathRemote = '/home/u747826271/domains/eurotaxisystem.site/public_html/';
const filesToUpload = [
  'database/migrations/2026_06_20_210000_add_accident_fields_to_rescue_requests.php',
  'app/Http/Controllers/Api/DriverAppController.php',
  'app/Http/Controllers/DriverBehaviorController.php',
  'app/Models/RescueRequest.php',
  'routes/api.php',
  'routes/web.php',
  'resources/views/layouts/app.blade.php',
  'resources/views/driver-behavior/index.blade.php',
  'app/Services/FirebasePushService.php'
];

async function upload() {
  try {
    await sftp.connect(config);
    console.log('Connected to SFTP');
    for (const file of filesToUpload) {
      await sftp.fastPut(file, basePathRemote + file);
      console.log('Uploaded: ' + file);
    }
  } catch (err) {
    console.error(err);
  } finally {
    sftp.end();
  }
}
upload();
