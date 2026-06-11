const { Client } = require('ssh2');
const fs = require('fs');

const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026',
    readyTimeout: 30000
};

const files = [
    {
        local: 'c:/xampp/htdocs/eurotaxisystem/resources/views/partials/chat-drawer.blade.php',
        remote: '/home/u747826271/domains/eurotaxisystem.site/public_html/resources/views/partials/chat-drawer.blade.php'
    }
];

const conn = new Client();
conn.on('ready', () => {
    console.log('SSH Connected!');
    conn.sftp((err, sftp) => {
        if (err) throw err;
        let done = 0;
        files.forEach(f => {
            sftp.fastPut(f.local, f.remote, {}, (err) => {
                if (err) console.error('ERROR:', f.local, err.message);
                else console.log('✅ UPLOADED:', f.remote);
                done++;
                if (done === files.length) {
                    console.log('\n✅ ALL DONE — Live na sa Hostinger!');
                    conn.end();
                }
            });
        });
    });
}).on('error', err => {
    console.error('Connection Error:', err.message);
}).connect(config);
