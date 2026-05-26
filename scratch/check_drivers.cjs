const { Client } = require('ssh2');

const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026',
    readyTimeout: 30000
};

const commands = [
    // Check driver users
    `mysql -h127.0.0.1 -uu747826271_eurotaxi -p'@Eurotaxi123' u747826271_eurotaxi -e "SELECT id, full_name, email, phone, role, is_active, deleted_at FROM users WHERE role='driver' ORDER BY id DESC LIMIT 10;"`,
    // Check if personal_access_tokens table exists and has records
    `mysql -h127.0.0.1 -uu747826271_eurotaxi -p'@Eurotaxi123' u747826271_eurotaxi -e "SELECT COUNT(*) as tokens FROM personal_access_tokens WHERE tokenable_type='App\\\\\\\\Models\\\\\\\\User';"`,
    // Check Laravel storage permissions
    `ls -la /home/u747826271/domains/eurotaxisystem.site/public_html/storage/`,
    // Check .env APP_KEY
    `grep APP_KEY /home/u747826271/domains/eurotaxisystem.site/public_html/.env | head -1`,
];

const conn = new Client();
let cmdIndex = 0;

function runNext() {
    if (cmdIndex >= commands.length) {
        conn.end();
        return;
    }
    const cmd = commands[cmdIndex++];
    console.log('\n--- CMD:', cmdIndex, '---');
    conn.exec(cmd, (err, stream) => {
        if (err) { console.error('Exec error:', err); runNext(); return; }
        stream.on('close', runNext)
              .on('data', d => process.stdout.write(d.toString()))
              .stderr.on('data', d => process.stderr.write('STDERR: ' + d.toString()));
    });
}

conn.on('ready', runNext)
    .on('error', e => console.error('Connection error:', e.message))
    .connect(config);
