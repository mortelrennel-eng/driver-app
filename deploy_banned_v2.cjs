const { Client } = require('ssh2');
const fs = require('fs');
const path = require('path');

const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026',
    readyTimeout: 30000,
    algorithms: {
        kex: ['diffie-hellman-group14-sha256', 'diffie-hellman-group14-sha1', 'diffie-hellman-group1-sha1'],
        cipher: ['aes128-ctr', 'aes192-ctr', 'aes256-ctr', 'aes128-gcm', 'aes256-gcm'],
        serverHostKey: ['ssh-rsa', 'ecdsa-sha2-nistp256'],
        hmac: ['hmac-sha2-256', 'hmac-sha1']
    }
};

const BASE_REMOTE = '/home/u747826271/domains/eurotaxisystem.site/public_html';
const BASE_LOCAL  = __dirname;

const filesToUpload = [
    // Controller — updated banned() to also pass $activeDrivers
    { local: 'app/Http/Controllers/DriverManagementController.php', remote: `${BASE_REMOTE}/app/Http/Controllers/DriverManagementController.php` },
    // Banned view — full rewrite with Add Ban/Suspend modal + Modify modal
    { local: 'resources/views/driver-management/banned.blade.php', remote: `${BASE_REMOTE}/resources/views/driver-management/banned.blade.php` },
];

const POST_COMMANDS = `cd ${BASE_REMOTE} && php artisan view:clear && php artisan route:clear && php artisan cache:clear && echo "---DONE---"`;

console.log('\n╔═══════════════════════════════════════════════════════╗');
console.log('║  DEPLOY: Ban/Suspend Modal in Banned Roster → Live   ║');
console.log('╚═══════════════════════════════════════════════════════╝\n');

const conn = new Client();
conn.on('ready', () => {
    console.log('✓ SSH Connected\n');
    conn.sftp((err, sftp) => {
        if (err) { console.error('SFTP Error:', err); conn.end(); return; }
        let done = 0;
        function next(i) {
            if (i >= filesToUpload.length) {
                console.log(`\n✓ ${done}/${filesToUpload.length} files uploaded\n`);
                console.log('Clearing caches on server...\n');
                sftp.end();
                conn.exec(POST_COMMANDS, (err, stream) => {
                    if (err) { console.error('Exec error:', err); conn.end(); return; }
                    stream.on('close', code => {
                        console.log(code === 0 ? '\n✓ DEPLOY COMPLETE! Live server updated.' : '\n⚠ Finished with code: ' + code);
                        console.log('→ https://eurotaxisystem.site/driver-management/banned\n');
                        conn.end();
                    }).on('data', d => process.stdout.write('  ' + d))
                      .stderr.on('data', d => process.stderr.write('  [ERR] ' + d));
                });
                return;
            }
            const f = filesToUpload[i];
            const lp = path.join(BASE_LOCAL, f.local);
            if (!fs.existsSync(lp)) { console.error(`✗ Not found: ${f.local}`); next(i+1); return; }
            const ws = sftp.createWriteStream(f.remote);
            ws.on('close', () => { done++; console.log(`  ✓ [${done}/${filesToUpload.length}] ${f.local}`); next(i+1); });
            ws.on('error', e => { console.error(`  ✗ ${f.local}: ${e.message}`); next(i+1); });
            ws.end(fs.readFileSync(lp));
        }
        next(0);
    });
}).on('error', err => console.error('✗ SSH Error:', err.message))
  .connect(config);
