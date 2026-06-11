const { Client } = require('ssh2');

const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026',
    readyTimeout: 30000
};

const conn = new Client();
conn.on('ready', () => {
    console.log('SSH Connected! Querying DB on live server...');
    const phpCmd = `php -r "
        require 'vendor/autoload.php';
        \\$app = require_once 'bootstrap/app.php';
        \\$kernel = \\$app->make(Illuminate\\\\Contracts\\\\Console\\\\Kernel::class);
        \\$kernel->bootstrap();
        use Illuminate\\\\Support\\\\Facades\\\\DB;
        \\$schema = DB::select('SHOW CREATE TABLE driver_behavior');
        print_r(\\$schema);
    "`;
    
    conn.exec(`cd /home/u747826271/domains/eurotaxisystem.site/public_html && ${phpCmd}`, (err, stream) => {
        if (err) {
            console.error('Execution Error:', err);
            conn.end();
            return;
        }
        stream.on('close', (code, signal) => {
            conn.end();
        }).on('data', (data) => {
            process.stdout.write(data);
        }).stderr.on('data', (data) => {
            process.stderr.write(data);
        });
    });
}).on('error', err => {
    console.error('Connection Error:', err.message);
}).connect(config);
