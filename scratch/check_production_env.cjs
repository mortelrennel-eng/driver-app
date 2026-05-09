const { Client } = require('ssh2');

const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026'
};

const commands = [
    // Print current DB_HOST to see what it is
    `grep "DB_HOST" /home/u747826271/domains/eurotaxisystem.site/public_html/.env`,
    // Change DB_HOST to localhost in .env on production server
    `sed -i "s/DB_HOST=.*/DB_HOST=localhost/" /home/u747826271/domains/eurotaxisystem.site/public_html/.env`,
    // Verify it changed
    `grep "DB_HOST" /home/u747826271/domains/eurotaxisystem.site/public_html/.env`,
    // Clear Laravel configuration cache to apply changes immediately
    `php /home/u747826271/domains/eurotaxisystem.site/public_html/artisan config:clear`,
    `php /home/u747826271/domains/eurotaxisystem.site/public_html/artisan cache:clear`
];

console.log('Connecting to Hostinger via SSH...');
const conn = new Client();
conn.on('ready', () => {
    console.log('SSH Connection Successful. Running DB_HOST adjustment commands...');
    conn.exec(commands.join(' && '), (err, stream) => {
        if (err) throw err;
        stream.on('close', (code) => {
            console.log(`Finished executing commands (code: ${code})!`);
            conn.end();
            process.exit(0);
        }).on('data', d => process.stdout.write(d)).stderr.on('data', d => process.stderr.write(d));
    });
}).on('error', err => {
    console.error('SSH Connection Failed:', err);
}).connect(config);
