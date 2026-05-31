const { Client } = require('ssh2');
const fs = require('fs');
const path = require('path');

const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026',
    readyTimeout: 30000
};

const BASE_REMOTE = '/home/u747826271/domains/eurotaxisystem.site/public_html';
const BASE_LOCAL = __dirname;

const filesToDownload = [
    'app/Http/Controllers/AnalyticsController.php',
    'app/Http/Controllers/LiveTrackingController.php',
    'app/Http/Controllers/MaintenanceController.php',
    'app/Http/Controllers/SparePartController.php',
    'app/Http/Controllers/UnitProfitabilityController.php',
    'app/Services/TracksolidService.php',
    'js/realtime-tracking.js',
    'public/js/realtime-tracking.js',
    'resources/views/analytics/index.blade.php',
    'resources/views/driver-management/partials/_drivers_table.blade.php',
    'routes/web.php',
    'resources/views/units/partials/_units_table.blade.php',
    'resources/views/units/partials/unit_details_modal.blade.php',
    'robots.txt',
    'sitemap.xml',
    'site.webmanifest',
    'sw.js',
    'site-under-construction.json',
    'tests/Feature/EuroTaxiSystemTest.php'
];

console.log('--- SFTP PULL START ---');
const conn = new Client();

conn.on('ready', () => {
    console.log('SSH Connection Ready. Starting SFTP...');
    conn.sftp((err, sftp) => {
        if (err) {
            console.error('SFTP Error:', err);
            conn.end();
            return;
        }

        let downloadCount = 0;

        function downloadNext(index) {
            if (index >= filesToDownload.length) {
                console.log(`\nAll ${downloadCount} files downloaded successfully!`);
                sftp.end();
                conn.end();
                return;
            }

            const file = filesToDownload[index];
            const remotePath = `${BASE_REMOTE}/${file}`;
            const localPath = path.join(BASE_LOCAL, file);
            const localDir = path.dirname(localPath);

            if (!fs.existsSync(localDir)) {
                fs.mkdirSync(localDir, { recursive: true });
            }

            sftp.fastGet(remotePath, localPath, (err) => {
                if (err) {
                    console.error(`✗ Failed: ${file} — ${err.message}`);
                } else {
                    downloadCount++;
                    console.log(`✓ [${downloadCount}/${filesToDownload.length}] ${file}`);
                }
                downloadNext(index + 1);
            });
        }

        downloadNext(0);
    });
}).on('error', (err) => {
    console.error('Connection Error:', err.message);
}).connect(config);
