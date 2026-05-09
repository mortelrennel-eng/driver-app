const { Client } = require('ssh2');

const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026',
    readyTimeout: 20000
};

const command = `echo "--- ROOT ---" && ls -la /home/u747826271 && echo "--- DOMAINS ---" && ls -la /home/u747826271/domains && echo "--- PUBLIC_HTML ---" && ls -la /home/u747826271/public_html`;

const conn = new Client();
conn.on('ready', () => {
    conn.exec(command, (err, stream) => {
        if (err) throw err;
        stream.on('close', () => conn.end())
              .on('data', data => process.stdout.write(data))
              .stderr.on('data', data => process.stderr.write(data));
    });
}).connect(config);
