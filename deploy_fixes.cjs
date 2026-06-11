const fs = require('fs');
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
    console.log('SSH connection established');
    conn.sftp((err, sftp) => {
        if (err) throw err;
        
        console.log('SFTP connected, reading local files...');
        
        const filesToUpload = [
            {
                local: 'c:/xampp/htdocs/eurotaxisystem/routes/web.php',
                remote: '/home/u747826271/domains/eurotaxisystem.site/public_html/routes/web.php'
            },
            {
                local: 'c:/xampp/htdocs/eurotaxisystem/app/Http/Controllers/AnalyticsController.php',
                remote: '/home/u747826271/domains/eurotaxisystem.site/public_html/app/Http/Controllers/AnalyticsController.php'
            },
            {
                local: 'c:/xampp/htdocs/eurotaxisystem/app/Http/Controllers/UnitProfitabilityController.php',
                remote: '/home/u747826271/domains/eurotaxisystem.site/public_html/app/Http/Controllers/UnitProfitabilityController.php'
            },
            {
                local: 'c:/xampp/htdocs/eurotaxisystem/app/Http/Controllers/MaintenanceController.php',
                remote: '/home/u747826271/domains/eurotaxisystem.site/public_html/app/Http/Controllers/MaintenanceController.php'
            },
            {
                local: 'c:/xampp/htdocs/eurotaxisystem/app/Http/Controllers/SparePartController.php',
                remote: '/home/u747826271/domains/eurotaxisystem.site/public_html/app/Http/Controllers/SparePartController.php'
            },
            {
                local: 'c:/xampp/htdocs/eurotaxisystem/resources/views/analytics/index.blade.php',
                remote: '/home/u747826271/domains/eurotaxisystem.site/public_html/resources/views/analytics/index.blade.php'
            }
        ];
        
        let completed = 0;
        filesToUpload.forEach(file => {
            sftp.fastPut(file.local, file.remote, (err) => {
                if (err) console.error('Error uploading ' + file.local, err);
                else console.log('Successfully uploaded ' + file.remote);
                
                completed++;
                if (completed === filesToUpload.length) conn.end();
            });
        });
    });
}).on('error', (err) => {
    console.error('SSH connection error:', err);
}).connect(config);
