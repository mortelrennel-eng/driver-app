const { Client } = require('ssh2');

const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026',
    readyTimeout: 20000
};

const command = 'ls -d /home/u747826271/domains/eurotaxisystem.site/public_html/scratch || mkdir -p /home/u747826271/domains/eurotaxisystem.site/public_html/scratch && chmod 777 /home/u747826271/domains/eurotaxisystem.site/public_html/scratch';

const conn = new Client();
conn.on('ready', () => {
    conn.exec(command, (err, stream) => {
        if (err) throw err;
        stream.on('data', (data) => {
            process.stdout.write(data.toString());
        }).on('close', () => {
            conn.end();
        });
    });
}).connect(config);
