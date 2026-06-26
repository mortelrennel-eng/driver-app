const Client = require('ssh2-sftp-client');
const path = require('path');

const sftp = new Client();
const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026',
    retries: 3,
    retry_delay: 5000,
    readyTimeout: 30000,
};

const baseLocal = 'c:\\xampp\\htdocs\\eurotaxisystem-main\\';
const baseRemote = '/home/u747826271/domains/eurotaxisystem.site/public_html/';

const filesToUpload = [
    'app/Http/Controllers/Api/DriverAppController.php',
    'app/Services/FirebasePushService.php',
    'app/Http/Controllers/DriverBehaviorController.php',
    'routes/api.php',
];

async function upload() {
    try {
        console.log('Connecting...');
        await sftp.connect(config);
        console.log('Connected!');
        for (const file of filesToUpload) {
            const local = path.join(baseLocal, file);
            const remote = baseRemote + file;
            await sftp.fastPut(local, remote);
            console.log('✅ Uploaded: ' + file);
        }
        console.log('All done!');
    } catch (err) {
        console.error('❌ Error:', err.message);
    } finally {
        sftp.end();
    }
}

upload();
