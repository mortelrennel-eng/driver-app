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
    { local: 'app/Http/Controllers/Api/DriverAppController.php', remote: 'app/Http/Controllers/Api/DriverAppController.php' }
];

async function upload() {
    try {
        console.log('Connecting...');
        await sftp.connect(config);
        console.log('Connected!');
        for (const fileObj of filesToUpload) {
            const local = path.join(baseLocal, fileObj.local);
            const remote = baseRemote + fileObj.remote;
            await sftp.fastPut(local, remote);
            console.log('✅ Uploaded: ' + fileObj.local);
        }
        console.log('All done!');
    } catch (err) {
        console.error('❌ Error:', err.message);
    } finally {
        sftp.end();
    }
}

upload();
