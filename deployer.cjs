const { Client } = require('ssh2');

const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026',
    readyTimeout: 20000
};

const command = 'cd /home/u747826271/domains/eurotaxisystem.site/public_html && git fetch origin main && git checkout origin/main -- app/Http/Controllers/MyAccountController.php resources/views/my-account/index.blade.php resources/views/layouts/app.blade.php routes/web.php app/Mail/EmailChangeRequested.php app/Mail/VerifyNewEmail.php resources/views/emails/email-change-requested.blade.php resources/views/emails/verify-new-email.blade.php database/migrations/2026_05_04_141234_add_email_change_fields_to_users_table.php app/Http/Controllers/SuperAdminController.php resources/views/super-admin/index.blade.php resources/views/units/index.blade.php resources/views/units/partials/_units_table.blade.php resources/views/unit-profitability/index.blade.php resources/views/live-tracking/index.blade.php app/Http/Controllers/DriverManagementController.php resources/views/driver-management/partials/_drivers_table.blade.php app/Http/Controllers/AuthController.php app/Http/Controllers/Api/AuthController.php && rsync -av --exclude="storage" public/ . && sed -i \'s|../vendor|vendor|g\' index.php && sed -i \'s|../bootstrap|bootstrap|g\' index.php && php artisan migrate --force && php artisan optimize:clear && php artisan view:clear && echo "---SUCCESS_DEPLOY---"';

console.log('--- ROBUST DEPLOYMENT START ---');
console.log(`Connecting to ${config.host}:${config.port} as ${config.username}...`);

const conn = new Client();

conn.on('ready', () => {
    console.log('SSH Connection Ready.');
    console.log('Executing deployment commands...');

    conn.exec(command, (err, stream) => {
        if (err) {
            console.error('Execution Error:', err);
            conn.end();
            return;
        }

        stream.on('close', (code, signal) => {
            console.log(`\nStream Closed with code: ${code}`);
            conn.end();
            if (code === 0) {
                console.log('--- FINAL SUCCESS ---');
            } else {
                console.log('--- DEPLOYMENT FAILED ---');
            }
        }).on('data', (data) => {
            process.stdout.write(`STDOUT: ${data}`);
        }).stderr.on('data', (data) => {
            process.stderr.write(`STDERR: ${data}`);
        });
    });
}).on('error', (err) => {
    console.error('Connection Error:', err);
}).connect(config);
