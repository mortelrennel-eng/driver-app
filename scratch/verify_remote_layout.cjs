const { Client } = require('ssh2');
const fs = require('fs');
require('dotenv').config({ path: 'c:/xampp/htdocs/eurotaxisystem-main/.env' });

const conn = new Client();
conn.on('ready', () => {
    console.log('SSH Ready');
    conn.exec('tail -n 100 public_html/resources/views/layouts/app.blade.php', (err, stream) => {
        if (err) throw err;
        stream.on('data', (data) => {
            console.log('SERVER CONTENT TAIL:\n' + data);
        }).on('close', () => {
            conn.end();
        });
    });
}).connect({
    host: process.env.REMOTE_HOST,
    port: process.env.REMOTE_PORT || 22,
    username: process.env.REMOTE_USER,
    password: process.env.REMOTE_PASSWORD
});
