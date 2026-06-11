const { Client } = require('ssh2');
const config = {
    host: '195.35.62.133', port: 65002,
    username: 'u747826271', password: '@Admineuro2026',
    readyTimeout: 30000,
    algorithms: {
        kex: ['diffie-hellman-group14-sha256','diffie-hellman-group14-sha1','diffie-hellman-group1-sha1'],
        cipher: ['aes128-ctr','aes192-ctr','aes256-ctr','aes128-gcm','aes256-gcm'],
        serverHostKey: ['ssh-rsa','ecdsa-sha2-nistp256'],
        hmac: ['hmac-sha2-256','hmac-sha1']
    }
};
const conn = new Client();
// check what imei validation lines are on the server right now
const cmd = "grep -n 'imei' /home/u747826271/domains/eurotaxisystem.site/public_html/app/Http/Controllers/UnitController.php";
conn.on('ready', () => {
    console.log('Connected');
    conn.exec(cmd, (err, stream) => {
        if (err) { console.error('exec error:', err); conn.end(); return; }
        stream.on('close', (code) => { console.log('Done, code:', code); conn.end(); })
              .on('data', d => process.stdout.write(d.toString()))
              .stderr.on('data', d => process.stderr.write(d.toString()));
    });
}).on('error', e => console.error('Conn error:', e.message))
  .on('timeout', () => console.error('Timed out'))
  .connect(config);
