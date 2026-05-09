const { Client } = require('ssh2');

const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026'
};

const conn = new Client();
conn.on('ready', () => {
    console.log('SSH Connection Ready. Performing Final Perfect Sync...');
    const commands = [
        'cd /home/u747826271/domains/eurotaxisystem.site/public_html',
        'git reset --hard origin/main',
        'git pull origin main',
        'composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev',
        'rsync -av --exclude="storage" public/ .',
        'sed -i "s|../vendor|vendor|g" index.php',
        'sed -i "s|../bootstrap|bootstrap|g" index.php',
        'php artisan migrate --force',
        'php artisan optimize:clear',
        'chmod -R 775 storage bootstrap/cache'
    ].join(' && ');

    conn.exec(commands, (err, stream) => {
        if (err) throw err;
        stream.on('close', (code, signal) => {
            console.log('Sync Complete with code: ' + code);
            conn.end();
        }).on('data', (data) => {
            console.log(data.toString());
        }).stderr.on('data', (data) => {
            console.error('SERVER ERROR: ' + data);
        });
    });
}).connect(config);
