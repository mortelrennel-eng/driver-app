const { Client } = require('ssh2');

const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026',
    readyTimeout: 30000
};

const BASE_REMOTE = '/home/u747826271/domains/eurotaxisystem.site/public_html';

const filesToCheck = [
    'routes/api.php',
    'resources/views/partials/chat-drawer.blade.php',
    'resources/views/support/index.blade.php',
    'app/Http/Controllers/Api/DriverAppController.php',
    'app/Http/Controllers/DriverBehaviorV2Controller.php',
    'app/Http/Controllers/DriverBehaviorController.php',
    'public/assets/js/tutorial.js',
    'public/sw.js'
];

const conn = new Client();

conn.on('ready', () => {
    const commands = filesToCheck.map(f => `ls -l ${BASE_REMOTE}/${f}`).join('; ');
    const cmd = `${commands}; echo "--- Driver App Assets ---"; ls -l ${BASE_REMOTE}/public/driver-app/assets | head -n 5`;
    
    conn.exec(cmd, (err, stream) => {
        if (err) throw err;
        stream.on('close', () => {
            conn.end();
        }).on('data', (data) => {
            process.stdout.write(data);
        }).stderr.on('data', (data) => {
            process.stderr.write(data);
        });
    });
}).connect(config);

