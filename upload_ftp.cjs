const ftp = require('basic-ftp');
const path = require('path');
const fs = require('fs');

async function upload() {
    const client = new ftp.Client(30000);
    client.ftp.verbose = false;

    try {
        console.log('Connecting via FTP...');
        await client.access({
            host: 'ftp.eurotaxisystem.site',
            port: 21,
            user: 'u747826271',
            password: '@Admineuro2026',
            secure: false
        });
        console.log('Connected!');

        const remoteBase = '/domains/eurotaxisystem.site/public_html/';
        const localBase = 'c:\\xampp\\htdocs\\eurotaxisystem-main\\';

        const files = [
            'app/Http/Controllers/Api/DriverAppController.php',
            'app/Http/Controllers/DriverManagementController.php',
            'resources/views/driver-management/partials/_driver_details_modal.blade.php',
        ];

        for (const file of files) {
            const localPath = path.join(localBase, file);
            const remotePath = remoteBase + file.replace(/\\/g, '/');
            const remoteDir = remotePath.substring(0, remotePath.lastIndexOf('/'));
            await client.ensureDir(remoteDir);
            await client.uploadFrom(localPath, remotePath);
            console.log('✅ Uploaded: ' + file);
        }

        console.log('All done!');
    } catch (err) {
        console.error('❌ FTP Error:', err.message);
    } finally {
        client.close();
    }
}

upload();
