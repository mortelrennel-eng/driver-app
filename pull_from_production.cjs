const { Client } = require('ssh2');
const fs = require('fs');
const path = require('path');

const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026'
};

const REMOTE_BASE = '/home/u747826271/domains/eurotaxisystem.site/public_html';
const LOCAL_BASE = process.cwd();

const DIRS_TO_PULL = [
    'app',
    'resources',
    'routes',
    'config',
    'database'
];

const conn = new Client();

conn.on('ready', () => {
    console.log('SSH Client Ready');
    conn.sftp((err, sftp) => {
        if (err) throw err;

        async function downloadDir(remoteDir, localDir) {
            if (!fs.existsSync(localDir)) {
                fs.mkdirSync(localDir, { recursive: true });
            }

            const list = await new Promise((resolve, reject) => {
                sftp.readdir(remoteDir, (err, list) => {
                    if (err) reject(err);
                    else resolve(list);
                });
            });

            for (const item of list) {
                const remotePath = path.posix.join(remoteDir, item.filename);
                const localPath = path.join(localDir, item.filename);

                if (item.attrs.isDirectory()) {
                    await downloadDir(remotePath, localPath);
                } else {
                    console.log(`Downloading: ${remotePath} -> ${localPath}`);
                    await new Promise((resolve, reject) => {
                        sftp.fastGet(remotePath, localPath, (err) => {
                            if (err) {
                                console.error(`Error downloading ${remotePath}: ${err.message}`);
                                resolve(); // Continue anyway
                            } else {
                                resolve();
                            }
                        });
                    });
                }
            }
        }

        async function run() {
            for (const dir of DIRS_TO_PULL) {
                console.log(`PULLING DIRECTORY: ${dir}`);
                try {
                    await downloadDir(`${REMOTE_BASE}/${dir}`, `${LOCAL_BASE}/${dir}`);
                } catch (e) {
                    console.error(`Failed to pull ${dir}: ${e.message}`);
                }
            }
            // Pull .env specifically
            console.log('PULLING .env');
            try {
                await new Promise((resolve, reject) => {
                    sftp.fastGet(`${REMOTE_BASE}/.env`, `${LOCAL_BASE}/.env_production`, (err) => {
                        if (err) resolve();
                        else resolve();
                    });
                });
            } catch(e) {}
            
            console.log('PULL COMPLETE');
            conn.end();
        }

        run();
    });
}).connect(config);
