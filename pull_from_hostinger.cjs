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

// Directories to SKIP (too large / auto-generated)
const SKIP_DIRS = [
    'node_modules',
    '.git',
];

let totalFiles = 0;
let doneFiles  = 0;
let errors     = 0;

function ensureDir(localPath) {
    if (!fs.existsSync(localPath)) {
        fs.mkdirSync(localPath, { recursive: true });
    }
}

function downloadFile(sftp, remotePath, localPath, cb) {
    sftp.fastGet(remotePath, localPath, {}, (err) => {
        if (err) {
            console.error(`  [ERROR] ${remotePath} -> ${err.message}`);
            errors++;
        } else {
            doneFiles++;
            if (doneFiles % 50 === 0) {
                console.log(`  [${doneFiles}/${totalFiles}] downloaded so far...`);
            }
        }
        cb();
    });
}

function walkRemote(sftp, remoteDir, localDir, done) {
    sftp.readdir(remoteDir, (err, list) => {
        if (err) {
            console.error(`  [READDIR ERROR] ${remoteDir}: ${err.message}`);
            return done();
        }

        ensureDir(localDir);

        let pending = list.length;
        if (pending === 0) return done();

        list.forEach(item => {
            const rPath = remoteDir + '/' + item.filename;
            const lPath = path.join(localDir, item.filename);

            if (item.attrs.isDirectory()) {
                // Skip certain directories
                if (SKIP_DIRS.includes(item.filename)) {
                    console.log(`  [SKIP DIR] ${rPath}`);
                    pending--;
                    if (pending === 0) done();
                    return;
                }
                walkRemote(sftp, rPath, lPath, () => {
                    pending--;
                    if (pending === 0) done();
                });
            } else {
                totalFiles++;
                downloadFile(sftp, rPath, lPath, () => {
                    pending--;
                    if (pending === 0) done();
                });
            }
        });
    });
}

console.log('==================================================');
console.log('  EURO TAXI - FULL PULL FROM HOSTINGER TO LOCAL');
console.log('==================================================');
console.log(`  Remote: ${REMOTE_ROOT}`);
console.log(`  Local:  ${LOCAL_ROOT}`);
console.log(`  Skipping dirs: ${SKIP_DIRS.join(', ')}`);
console.log('');
console.log('Connecting...');

const conn = new Client();

conn.on('ready', () => {
    console.log('SSH Connected! Starting SFTP download...\n');
    conn.sftp((err, sftp) => {
        if (err) {
            console.error('SFTP Error:', err);
            conn.end();
            return;
        }

        const startTime = Date.now();
        walkRemote(sftp, REMOTE_ROOT, LOCAL_ROOT, () => {
            const elapsed = ((Date.now() - startTime) / 1000).toFixed(1);
            console.log('\n==================================================');
            console.log(`  DONE! ${doneFiles} files downloaded in ${elapsed}s`);
            if (errors > 0) console.log(`  WARNINGS: ${errors} file(s) had errors`);
            console.log('==================================================');
            conn.end();
        });
    });
}).on('error', (err) => {
    console.error('Connection Error:', err.message);
}).connect(config);
