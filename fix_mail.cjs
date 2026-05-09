const { Client } = require('ssh2');

const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026'
};

const conn = new Client();
conn.on('ready', () => {
    console.log('SSH Connection Ready. Updating mail config...');
    const commands = [
        'cd /home/u747826271/domains/eurotaxisystem.site/public_html',
        'sed -i "s/MAIL_PORT=465/MAIL_PORT=587/g" .env',
        'sed -i "s/MAIL_ENCRYPTION=ssl/MAIL_ENCRYPTION=tls/g" .env',
        'php artisan config:clear'
    ].join(' && ');

    conn.exec(commands, (err, stream) => {
        if (err) throw err;
        stream.on('close', (code, signal) => {
            console.log('Update Complete with code: ' + code);
            conn.end();
        }).on('data', (data) => {
            console.log(data.toString());
        }).stderr.on('data', (data) => {
            console.error('ERROR: ' + data);
        });
    });
}).connect(config);
