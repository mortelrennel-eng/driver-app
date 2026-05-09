const { Client } = require('ssh2');

const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026'
};

const commands = [
    `sed -i "s/TRACKSOLID_APP_KEY=.*/TRACKSOLID_APP_KEY=8FB345B8693CCD00149EBCB96D0EAE85339A22A4105B6558/" /home/u747826271/domains/eurotaxisystem.site/public_html/.env`,
    `sed -i "s/TRACKSOLID_APP_SECRET=.*/TRACKSOLID_APP_SECRET=9ce8f4e1fe3b430c8b94f24aa83b809c/" /home/u747826271/domains/eurotaxisystem.site/public_html/.env`,
    `sed -i "s/TRACKSOLID_USERNAME=.*/TRACKSOLID_USERNAME=Admin_shiellamarie/" /home/u747826271/domains/eurotaxisystem.site/public_html/.env`,
    `sed -i "s/TRACKSOLID_PASSWORD=.*/TRACKSOLID_PASSWORD=3406d9a5d03ec8d3c3c7b433eee0a8a7/" /home/u747826271/domains/eurotaxisystem.site/public_html/.env`,
    `sed -i "s/SEMAPHORE_API_KEY=.*/SEMAPHORE_API_KEY=272909df95197f31f173d31f3ded2df4/" /home/u747826271/domains/eurotaxisystem.site/public_html/.env`,
    `php /home/u747826271/domains/eurotaxisystem.site/public_html/artisan config:clear`
];

console.log('Fixing Hostinger .env Configuration...');
const conn = new Client();
conn.on('ready', () => {
    conn.exec(commands.join(' && '), (err, stream) => {
        if (err) throw err;
        stream.on('close', () => {
            console.log('Finished updating .env and cleared config cache!');
            conn.end();
            process.exit(0);
        }).on('data', d => process.stdout.write(d)).stderr.on('data', d => process.stderr.write(d));
    });
}).connect(config);
