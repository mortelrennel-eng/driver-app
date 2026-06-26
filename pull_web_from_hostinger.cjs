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
const LOCAL_ROOT  = __dirname; // c:\xampp\htdocs\eurotaxisystem-main

// ============================================================
//  DIRECTORIES TO PULL (web-only, no driver app)
// ============================================================
const DIRS_TO_PULL = [
    'app',
    'resources',
    'routes',
    'config',
    'database',
    'public',
    'bootstrap',
    'build',
    'js',
    'lang',
    'storage/framework/views', // compiled views
];

// ============================================================
//  DIRECTORIES TO ALWAYS SKIP (even if nested)
// ============================================================
const SKIP_DIRS = new Set([
    'driver-app',       // <<< NEVER TOUCH
    'android-app',
    'mobile-ionic',
    'node_modules',
    '.git',
    '.github',
    'vendor',           // composer vendor – too large
]);

// ============================================================
//  ROOT-LEVEL FILES TO ALSO PULL
// ============================================================
const ROOT_FILES = [
    '.env',
    '.htaccess',
    'composer.json',
    'composer.lock',
    'package.json',
    'vite.config.js',
    'tailwind.config.js',
    'postcss.config.js',
    'artisan',
    'index.php',
    'robots.txt',
    'sitemap.xml',
    'sw.js',
    'manifest.json',
    'site.webmanifest',
    'version.txt',
];

let totalFiles = 0;
let downloadedFiles = 0;
let skippedFiles = 0;
let errorCount = 0;
const changedFiles = [];

function ensureDir(localPath) {
    if (!fs.existsSync(localPath)) {
        fs.mkdirSync(localPath, { recursive: true });
    }
}

// ============================================================
//  DOWNLOAD HELPERS
// ============================================================
function downloadFile(sftp, remotePath, localPath) {
    return new Promise((resolve) => {
        sftp.fastGet(remotePath, localPath, {}, (err) => {
            if (err) {
                console.error(`  ✗ ERROR: ${remotePath} → ${err.message}`);
                errorCount++;
            } else {
                downloadedFiles++;
                changedFiles.push(remotePath.replace(REMOTE_ROOT + '/', ''));
                if (downloadedFiles % 25 === 0) {
                    console.log(`  ... ${downloadedFiles}/${totalFiles} files downloaded`);
                }
            }
            resolve();
        });
    });
}

async function walkAndDownload(sftp, remoteDir, localDir) {
    const list = await new Promise((resolve, reject) => {
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
                console.log(`  ⊘ SKIPPED DIR: ${rPath}`);
                skippedFiles++;
                continue;
            }
            await walkAndDownload(sftp, rPath, lPath);
        } else {
            totalFiles++;
            await downloadFile(sftp, rPath, lPath);
        }
    }
}

// ============================================================
//  MAIN
// ============================================================
console.log('╔══════════════════════════════════════════════════════════╗');
console.log('║  EURO TAXI — PULL WEB CODE FROM HOSTINGER               ║');
console.log('║  ⚠  driver-app WILL NOT BE TOUCHED                     ║');
console.log('╚══════════════════════════════════════════════════════════╝');
console.log(`  Remote : ${REMOTE_ROOT}`);
console.log(`  Local  : ${LOCAL_ROOT}`);
console.log(`  Skip   : ${[...SKIP_DIRS].join(', ')}`);
console.log('');
console.log('Connecting via SSH...');

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

        // 1) Pull directories
        for (const dir of DIRS_TO_PULL) {
            console.log(`\n📂 Pulling directory: ${dir}`);
            try {
                await walkAndDownload(
                    sftp,
                    `${REMOTE_ROOT}/${dir}`,
                    path.join(LOCAL_ROOT, dir)
                );
            } catch (e) {
                console.error(`  ✗ Failed to pull ${dir}: ${e.message}`);
            }
        }

        // 2) Pull root-level files
        console.log('\n📄 Pulling root-level files...');
        for (const file of ROOT_FILES) {
            const remotePath = `${REMOTE_ROOT}/${file}`;
            const localPath  = path.join(LOCAL_ROOT, file);
            totalFiles++;
            await downloadFile(sftp, remotePath, localPath);
        }

        const elapsed = ((Date.now() - startTime) / 1000).toFixed(1);

        console.log('\n╔══════════════════════════════════════════════════════════╗');
        console.log(`║  DONE! ${downloadedFiles} files downloaded in ${elapsed}s`);
        if (errorCount > 0) console.log(`║  ⚠ ${errorCount} file(s) had errors`);
        if (skippedFiles > 0) console.log(`║  ⊘ ${skippedFiles} directories skipped`);
        console.log('╚══════════════════════════════════════════════════════════╝');

        // Save a log of changed files
        const logPath = path.join(LOCAL_ROOT, 'last_pull_log.txt');
        const logContent = `Pull from Hostinger — ${new Date().toISOString()}\n` +
            `Downloaded: ${downloadedFiles}\n` +
            `Errors: ${errorCount}\n` +
            `Skipped dirs: ${skippedFiles}\n\n` +
            `Files:\n${changedFiles.join('\n')}\n`;
        fs.writeFileSync(logPath, logContent);
        console.log(`\n📝 Log saved to: ${logPath}`);

        conn.end();
    });
}).on('error', (err) => {
    console.error('Connection Error:', err.message);
}).connect(config);
