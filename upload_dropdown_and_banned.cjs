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
        local: 'c:/xampp/htdocs/eurotaxisystem/routes/web.php',
        remote: '/home/u747826271/domains/eurotaxisystem.site/public_html/routes/web.php'
    },
    {
        local: 'c:/xampp/htdocs/eurotaxisystem/app/Http/Controllers/DriverManagementController.php',
        remote: '/home/u747826271/domains/eurotaxisystem.site/public_html/app/Http/Controllers/DriverManagementController.php'
    },
    {
        local: 'c:/xampp/htdocs/eurotaxisystem/resources/views/layouts/app.blade.php',
        remote: '/home/u747826271/domains/eurotaxisystem.site/public_html/resources/views/layouts/app.blade.php'
    },
    {
        local: 'c:/xampp/htdocs/eurotaxisystem/resources/views/driver-management/banned.blade.php',
        remote: '/home/u747826271/domains/eurotaxisystem.site/public_html/resources/views/driver-management/banned.blade.php'
    },
    {
        local: 'c:/xampp/htdocs/eurotaxisystem/resources/views/driver-management/index.blade.php',
        remote: '/home/u747826271/domains/eurotaxisystem.site/public_html/resources/views/driver-management/index.blade.php'
    },
    {
        local: 'c:/xampp/htdocs/eurotaxisystem/resources/views/driver-management/partials/_driver_details_modal.blade.php',
        remote: '/home/u747826271/domains/eurotaxisystem.site/public_html/resources/views/driver-management/partials/_driver_details_modal.blade.php'
    },
    {
        local: 'c:/xampp/htdocs/eurotaxisystem/database/migrations/2026_06_02_175044_add_suspension_fields_to_drivers_table.php',
        remote: '/home/u747826271/domains/eurotaxisystem.site/public_html/database/migrations/2026_06_02_175044_add_suspension_fields_to_drivers_table.php'
    },
    {
        local: 'c:/xampp/htdocs/eurotaxisystem/app/Providers/AppServiceProvider.php',
        remote: '/home/u747826271/domains/eurotaxisystem.site/public_html/app/Providers/AppServiceProvider.php'
    },
    {
        local: 'c:/xampp/htdocs/eurotaxisystem/resources/views/driver-management/partials/_drivers_table.blade.php',
        remote: '/home/u747826271/domains/eurotaxisystem.site/public_html/resources/views/driver-management/partials/_drivers_table.blade.php'
    },
    {
        local: 'c:/xampp/htdocs/eurotaxisystem/database/migrations/2026_06_02_182619_change_notes_to_text_in_login_audit_table.php',
        remote: '/home/u747826271/domains/eurotaxisystem.site/public_html/database/migrations/2026_06_02_182619_change_notes_to_text_in_login_audit_table.php'
    },
    {
        local: 'c:/xampp/htdocs/eurotaxisystem/app/Models/LoginAudit.php',
        remote: '/home/u747826271/domains/eurotaxisystem.site/public_html/app/Models/LoginAudit.php'
    },
    {
        local: 'c:/xampp/htdocs/eurotaxisystem/database/migrations/2026_06_03_200500_make_unit_id_nullable_in_driver_behavior_table.php',
        remote: '/home/u747826271/domains/eurotaxisystem.site/public_html/database/migrations/2026_06_03_200500_make_unit_id_nullable_in_driver_behavior_table.php'
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
