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
const LOCAL_ROOT  = __dirname;

const DIRS_TO_PULL = [
    'app',
    'resources',
    'routes',
    'config',
    'database',
    'public',
    'bootstrap',
];

const SKIP_DIRS = new Set([
    'driver-app',
    'android-app',
    'mobile-ionic',
    'node_modules',
    '.git',
    '.github',
    'vendor',
]);

const EXACT_EXCLUDES = new Set([
    'AuthController.php',
    'RescueRequest.php',
    'DriverAppController.php'
]);

const ROOT_FILES = [
    '.env',
    '.htaccess',
    'composer.json',
    'artisan',
    'index.php',
];

let totalChecked = 0;
let downloaded = 0;
let skipped = 0;
let errors = 0;
const pulledFiles = [];
const skippedFiles = [];

function ensureDir(localPath) {
    if (!fs.existsSync(localPath)) {
        fs.mkdirSync(localPath, { recursive: true });
    }
}

function getLocalMtime(localPath) {
    try {
        if (fs.existsSync(localPath)) {
            return Math.floor(fs.statSync(localPath).mtimeMs / 1000);
        }
    } catch (e) {}
    return 0;
}

function downloadFile(sftp, remotePath, localPath) {
    return new Promise((resolve) => {
        sftp.fastGet(remotePath, localPath, {}, (err) => {
            if (err) {
                console.error(`  ✗ ERROR: ${remotePath} → ${err.message}`);
                errors++;
            } else {
                downloaded++;
                pulledFiles.push(remotePath.replace(REMOTE_ROOT + '/', ''));
            }
            resolve();
        });
    });
}

async function walkAndDownload(sftp, remoteDir, localDir) {
    const list = await new Promise((resolve) => {
        sftp.readdir(remoteDir, (err, items) => {
            if (err) {
                console.error(`  ✗ READDIR ERROR: ${remoteDir}: ${err.message}`);
                resolve([]);
            } else {
                resolve(items);
            }
        });
    });

    ensureDir(localDir);

    for (const item of list) {
        const rPath = remoteDir + '/' + item.filename;
        const lPath = path.join(localDir, item.filename);

        if (item.attrs.isDirectory()) {
            if (SKIP_DIRS.has(item.filename)) {
                console.log(`  ⊘ SKIPPED DIR: ${item.filename}`);
                continue;
            }
            await walkAndDownload(sftp, rPath, lPath);
        } else {
            if (EXACT_EXCLUDES.has(item.filename)) {
                console.log(`  ⊘ PROTECTED (Skipped): ${item.filename}`);
                continue;
            }
            totalChecked++;
            const remoteMtime = item.attrs.mtime || 0;
            const localMtime = getLocalMtime(lPath);

            if (remoteMtime > localMtime) {
                // Remote is newer — download it
                const relPath = rPath.replace(REMOTE_ROOT + '/', '');
                console.log(`  ↓ NEWER: ${relPath} (remote: ${new Date(remoteMtime * 1000).toISOString()} > local: ${localMtime ? new Date(localMtime * 1000).toISOString() : 'NOT EXISTS'})`);
                await downloadFile(sftp, rPath, lPath);
            } else {
                // Local is same or newer — skip
                skipped++;
            }
        }
    }
}

async function checkAndDownloadFile(sftp, remotePath, localPath) {
    return new Promise((resolve) => {
        sftp.stat(remotePath, async (err, stats) => {
            if (err) {
                console.log(`  ✗ Not found on remote: ${remotePath}`);
                resolve();
                return;
            }
            totalChecked++;
            const remoteMtime = stats.mtime || 0;
            const localMtime = getLocalMtime(localPath);

            if (remoteMtime > localMtime) {
                const relPath = remotePath.replace(REMOTE_ROOT + '/', '');
                console.log(`  ↓ NEWER: ${relPath}`);
                await downloadFile(sftp, remotePath, localPath);
            } else {
                skipped++;
                const relPath = remotePath.replace(REMOTE_ROOT + '/', '');
                skippedFiles.push(relPath + ' (local is up-to-date)');
            }
            resolve();
        });
    });
}

console.log('╔══════════════════════════════════════════════════════════╗');
console.log('║  EURO TAXI — SMART PULL (only newer files from Hostinger)║');
console.log('║  ⚠  driver-app WILL NOT BE TOUCHED                      ║');
console.log('╚══════════════════════════════════════════════════════════╝\n');

const conn = new Client();

conn.on('ready', () => {
    console.log('✓ SSH connected! Starting SFTP...\n');
    conn.sftp(async (err, sftp) => {
        if (err) {
            console.error('SFTP Error:', err);
            conn.end();
            return;
        }

        const startTime = Date.now();

        for (const dir of DIRS_TO_PULL) {
            console.log(`\n📂 Checking directory: ${dir}`);
            try {
                await walkAndDownload(sftp, `${REMOTE_ROOT}/${dir}`, path.join(LOCAL_ROOT, dir));
            } catch (e) {
                console.error(`  ✗ Failed: ${e.message}`);
            }
        }

        console.log('\n📄 Checking root-level files...');
        for (const file of ROOT_FILES) {
            await checkAndDownloadFile(sftp, `${REMOTE_ROOT}/${file}`, path.join(LOCAL_ROOT, file));
        }

        const elapsed = ((Date.now() - startTime) / 1000).toFixed(1);

        console.log('\n╔══════════════════════════════════════════════════════════╗');
        console.log(`║  DONE in ${elapsed}s`);
        console.log(`║  Checked  : ${totalChecked} files`);
        console.log(`║  Downloaded (remote newer): ${downloaded} files`);
        console.log(`║  Skipped (already up-to-date): ${skipped} files`);
        if (errors > 0) console.log(`║  ⚠ Errors: ${errors}`);
        console.log('╚══════════════════════════════════════════════════════════╝');

        if (pulledFiles.length > 0) {
            console.log('\n📥 Files pulled from Hostinger:');
            pulledFiles.forEach(f => console.log(`   ✓ ${f}`));
        } else {
            console.log('\n✅ All local files are already up-to-date! Nothing was pulled.');
        }

        const logPath = path.join(LOCAL_ROOT, 'last_smart_pull_log.txt');
        fs.writeFileSync(logPath, `Smart Pull — ${new Date().toISOString()}\nPulled: ${downloaded}\nSkipped: ${skipped}\nErrors: ${errors}\n\nPulled Files:\n${pulledFiles.join('\n')}\n\nUp-to-date (skipped):\n${skippedFiles.slice(0, 50).join('\n')}\n`);
        console.log(`\n📝 Log saved to: last_smart_pull_log.txt`);

        conn.end();
    });
}).on('error', (err) => {
    console.error('Connection Error:', err.message);
}).connect(config);
