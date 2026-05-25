const { Client } = require('ssh2');

const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026',
};

const conn = new Client();

conn.on('ready', () => {
    console.log('SSH Ready. Moving images...');
    
    const cmd = "mv /home/u747826271/domains/eurotaxisystem.site/public_html/public/uploads/support_attachments/* /home/u747826271/domains/eurotaxisystem.site/public_html/uploads/support_attachments/";

    conn.exec(cmd, (err, stream) => {
        if (err) throw err;
        stream.on('close', () => {
            console.log('Move complete.');
            conn.end();
        })
        .on('data', (data) => process.stdout.write(data))
        .stderr.on('data', (data) => process.stderr.write(data));
    });
}).connect(config);
