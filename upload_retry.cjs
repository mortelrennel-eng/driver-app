const Client = require('ssh2-sftp-client');
const path = require('path');

const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026',
    readyTimeout: 30000,
    retries: 5,
    retry_delay: 10000,
};

const localBase = 'c:\\xampp\\htdocs\\eurotaxisystem-main\\';
const remoteBase = '/home/u747826271/domains/eurotaxisystem.site/public_html/';

const files = [
    'resources/views/driver-behavior/index.blade.php',
    'app/Http/Controllers/Api/DriverAppController.php',
    'app/Http/Controllers/DriverBehaviorController.php',
    'routes/web.php',
];

const sleep = (ms) => new Promise(r => setTimeout(r, ms));

async function tryUpload(attempt = 1) {
    const sftp = new Client();
    try {
        console.log(`[Attempt ${attempt}] Connecting via SFTP...`);
        await sftp.connect(config);
        console.log('Connected!');

        for (const file of files) {
            const local = path.join(localBase, file);
            const remote = remoteBase + file;
            await sftp.fastPut(local, remote);
            console.log('✅ ' + file);
        }
        console.log('\n🎉 All files uploaded successfully!');
        await sftp.end();
        return true;
    } catch (err) {
        console.error(`❌ Attempt ${attempt} failed: ${err.message}`);
        try { await sftp.end(); } catch(_) {}
        if (attempt < 8) {
            const delay = attempt * 30000; // 30s, 60s, 90s...
            console.log(`Waiting ${delay/1000}s before retry...`);
            await sleep(delay);
            return tryUpload(attempt + 1);
        }
        console.error('All attempts exhausted.');
        return false;
    }
}

tryUpload();
