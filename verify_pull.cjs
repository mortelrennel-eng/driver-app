const { Client } = require('ssh2');
const fs = require('fs');
const path = require('path');

const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026',
    readyTimeout: 30000
};

const REMOTE_ROOT = '/home/u747826271/domains/eurotaxisystem.site/public_html';
const LOCAL_ROOT  = 'c:/xampp/htdocs/eurotaxisystem';

const SKIP_DIRS = ['node_modules', '.git'];

// Key files that MUST exist locally
const KEY_FILES = [
    'app/Http/Controllers/DashboardController.php',
    'app/Http/Controllers/MaintenanceController.php',
    'app/Http/Controllers/UnitController.php',
    'app/Http/Controllers/BoundaryController.php',
    'app/Http/Controllers/AnalyticsController.php',
    'app/Services/NotificationService.php',
    'app/Providers/AppServiceProvider.php',
    'resources/views/layouts/app.blade.php',
    'routes/web.php',
    'routes/api.php',
    '.env',
    'composer.json',
    'artisan',
];

let remoteCount = 0;
let missingLocally = [];
let remoteFiles = [];

function countRemote(sftp, remoteDir, done) {
    sftp.readdir(remoteDir, (err, list) => {
        if (err) return done();

        let pending = list.length;
        if (pending === 0) return done();

        list.forEach(item => {
            const rPath = remoteDir + '/' + item.filename;
            if (item.attrs.isDirectory()) {
                if (SKIP_DIRS.includes(item.filename)) {
                    pending--;
                    if (pending === 0) done();
                    return;
                }
                countRemote(sftp, rPath, () => {
                    pending--;
                    if (pending === 0) done();
                });
            } else {
                remoteCount++;
                // Store relative path
                const relPath = rPath.replace(REMOTE_ROOT + '/', '');
                remoteFiles.push(relPath);
                pending--;
                if (pending === 0) done();
            }
        });
    });
}

console.log('==============================================');
console.log('  VERIFY: Hostinger vs Local File Comparison');
console.log('==============================================\n');

const conn = new Client();
conn.on('ready', () => {
    console.log('SSH Connected. Counting remote files...\n');
    conn.sftp((err, sftp) => {
        if (err) { console.error(err); conn.end(); return; }

        countRemote(sftp, REMOTE_ROOT, () => {
            console.log(`Remote file count (Hostinger): ${remoteCount}`);

            // Check key files locally
            console.log('\n--- Checking KEY FILES locally ---');
            let allGood = true;
            KEY_FILES.forEach(f => {
                const localPath = path.join(LOCAL_ROOT, f);
                const exists = fs.existsSync(localPath);
                const size = exists ? fs.statSync(localPath).size : 0;
                const status = exists ? `OK (${size} bytes)` : 'MISSING!';
                if (!exists) { missingLocally.push(f); allGood = false; }
                console.log(`  [${exists ? '✓' : '✗'}] ${f} — ${status}`);
            });

            // Check a sample of remote files locally
            console.log('\n--- Spot-checking 20 random remote files locally ---');
            const sample = remoteFiles.sort(() => 0.5 - Math.random()).slice(0, 20);
            let missingCount = 0;
            sample.forEach(relPath => {
                const localPath = path.join(LOCAL_ROOT, relPath);
                const exists = fs.existsSync(localPath);
                if (!exists) {
                    console.log(`  [✗] MISSING: ${relPath}`);
                    missingCount++;
                } else {
                    console.log(`  [✓] ${relPath}`);
                }
            });

            console.log('\n==============================================');
            console.log(`  REMOTE Total Files : ${remoteCount}`);
            console.log(`  KEY FILES missing  : ${missingLocally.length}`);
            console.log(`  Spot-check missing : ${missingCount} / 20`);
            if (allGood && missingCount === 0) {
                console.log('  STATUS: ✅ COMPLETE — Local is in sync with Hostinger!');
            } else {
                console.log('  STATUS: ⚠️  Some files may be missing locally!');
            }
            console.log('==============================================');
            conn.end();
        });
    });
}).on('error', err => {
    console.error('Connection Error:', err.message);
}).connect(config);
