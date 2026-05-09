const { Client } = require('ssh2');

const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026',
    readyTimeout: 20000
};

const command = `cd /home/u747826271/domains/eurotaxisystem.site/public_html && rm -f bootstrap/cache/*.php && php artisan config:clear && php artisan cache:clear && echo "---CACHE_NUKED---"`;

const conn = new Client();
conn.on('ready', () => {
    conn.exec(command, (err, stream) => {
        if (err) throw err;
        stream.on('close', () => conn.end())
              .on('data', data => process.stdout.write(data))
              .stderr.on('data', data => process.stderr.write(data));
    });
}).connect(config);
