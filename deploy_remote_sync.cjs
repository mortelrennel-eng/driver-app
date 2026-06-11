const { Client } = require('ssh2');

const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026',
    readyTimeout: 30000
};

const commands = [
    'cd /home/u747826271/domains/eurotaxisystem.site/public_html',
    'echo "Fetching latest changes from GitHub..."',
    'git fetch origin main 2>&1',
    'echo "Resetting code to match local main branch..."',
    'git reset --hard origin/main 2>&1',
    'echo "Running database migrations..."',
    'php artisan migrate --force 2>&1',
    'echo "Clearing and generating new caches..."',
    'php artisan optimize:clear 2>&1',
    'php artisan optimize 2>&1',
    'php artisan view:clear 2>&1',
    'echo "--- DEPLOYMENT SUCCESSFUL ---"'
].join(' && ');

console.log('--- STARTING HOSTINGER REMOTE DEPLOY SYNC ---');
console.log(`Connecting to ${config.host}:${config.port}...`);

const conn = new Client();

conn.on('ready', () => {
    console.log('SSH Connection Established! Running sync commands...');
    
    conn.exec(commands, (err, stream) => {
        if (err) {
            console.error('Execution Error:', err);
            conn.end();
            return;
        }
        
        stream.on('close', (code, signal) => {
            console.log(`\nStream closed with exit code: ${code}`);
            conn.end();
            if (code === 0) {
                console.log('✅ Remote Hostinger server synchronized successfully!');
            } else {
                console.log('❌ Remote deployment failed.');
            }
        }).on('data', (data) => {
            process.stdout.write(data.toString());
        }).stderr.on('data', (data) => {
            process.stderr.write('STDERR: ' + data.toString());
        });
    });
}).on('error', (err) => {
    console.error('Connection Error:', err);
}).connect(config);
