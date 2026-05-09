const ssh2Path = 'C:/xampp/htdocs/eurotaxisystem/node_modules/ssh2';
const { Client } = require(ssh2Path);

const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026',
    readyTimeout: 20000
};

const command = `cd /home/u747826271/domains/eurotaxisystem.site/public_html && echo "--- PUBLIC FOLDER ---" && ls -la public && echo "--- ASSETS FOLDER ---" && ls -la public/assets`;

const conn = new Client();
conn.on('ready', () => {
    conn.exec(command, (err, stream) => {
        if (err) throw err;
        stream.on('close', () => conn.end())
              .on('data', data => process.stdout.write(data))
              .stderr.on('data', data => process.stderr.write(data));
    });
}).connect(config);
