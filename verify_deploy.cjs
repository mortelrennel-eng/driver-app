const { Client } = require('ssh2');
const conn = new Client();
conn.on('ready', () => {
    conn.exec('grep -n "HIGHEST PRIORITY\\|getActiveUnit\\|Priority 1" /home/u747826271/domains/eurotaxisystem.site/public_html/app/Http/Controllers/Api/DriverAppController.php', (err, stream) => {
        if (err) { console.log('ERR:', err); conn.end(); return; }
        stream.on('data', d => process.stdout.write(d.toString()));
        stream.stderr.on('data', d => process.stderr.write(d.toString()));
        stream.on('close', () => conn.end());
    });
}).connect({
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026',
    algorithms: {
        kex: ['diffie-hellman-group14-sha256', 'diffie-hellman-group14-sha1'],
        cipher: ['aes128-ctr', 'aes256-ctr'],
        serverHostKey: ['ssh-rsa', 'ecdsa-sha2-nistp256']
    }
});
