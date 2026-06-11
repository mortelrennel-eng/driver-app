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
        local: 'c:/xampp/htdocs/eurotaxisystem/resources/views/driver-management/index.blade.php',
        remote: '/home/u747826271/domains/eurotaxisystem.site/public_html/resources/views/driver-management/index.blade.php'
    },
    {
        local: 'c:/xampp/htdocs/eurotaxisystem/app/Http/Controllers/DriverManagementController.php',
        remote: '/home/u747826271/domains/eurotaxisystem.site/public_html/app/Http/Controllers/DriverManagementController.php'
    },
    {
        local: 'c:/xampp/htdocs/eurotaxisystem/app/Http/Controllers/DriverBehaviorController.php',
        remote: '/home/u747826271/domains/eurotaxisystem.site/public_html/app/Http/Controllers/DriverBehaviorController.php'
    },
    {
        local: 'c:/xampp/htdocs/eurotaxisystem/app/Traits/CalculatesDriverPerformance.php',
        remote: '/home/u747826271/domains/eurotaxisystem.site/public_html/app/Traits/CalculatesDriverPerformance.php'
    }
];

const conn = new Client();
conn.on('ready', () => {
    console.log('SSH Connected! Starting upload...');
    conn.sftp((err, sftp) => {
        if (err) {
            console.error('SFTP Error:', err);
            conn.end();
            return;
        }
        let done = 0;
        files.forEach(f => {
            sftp.fastPut(f.local, f.remote, {}, (err) => {
                if (err) {
                    console.error('ERROR uploading:', f.local, err.message);
                } else {
                    console.log('✅ UPLOADED:', f.remote);
                }
                done++;
                if (done === files.length) {
                    console.log('\nAll files uploaded successfully!');
                    console.log('Clearing view and cache...');
                    conn.exec('cd /home/u747826271/domains/eurotaxisystem.site/public_html && php artisan view:clear && php artisan cache:clear', (err, stream) => {
                        if (err) {
                            console.error('Artisan Command Error:', err);
                            conn.end();
                            return;
                        }
                        stream.on('close', (code, signal) => {
                            console.log(`✅ Cache cleared with code: ${code}`);
                            conn.end();
                        }).on('data', (data) => {
                            process.stdout.write(data);
                        }).stderr.on('data', (data) => {
                            process.stderr.write(data);
                        });
                    });
                }
            });
        });
    });
}).on('error', err => {
    console.error('Connection Error:', err.message);
}).connect(config);
