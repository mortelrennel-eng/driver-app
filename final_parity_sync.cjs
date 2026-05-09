const { Client } = require('ssh2');
const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026',
    readyTimeout: 30000
};

const BASE_REMOTE = '/home/u747826271/domains/eurotaxisystem.site/public_html/public/driver-app';
const BASE_LOCAL  = path.join(__dirname, 'driver-app', 'dist');

// 1. Build the app
console.log('Building driver-app...');
try {
    execSync('npm run build', { cwd: path.join(__dirname, 'driver-app'), stdio: 'inherit' });
} catch (e) {
    console.error('Build failed. Check for errors.');
    process.exit(1);
}

// 2. Sync to Hostinger
const conn = new Client();

async function uploadFile(sftp, localFile, remoteFile) {
    return new Promise((resolve, reject) => {
        const normalizedRemote = remoteFile.replace(/\\/g, '/');
        sftp.fastPut(localFile, normalizedRemote, (err) => {
            if (err) reject(err);
            else resolve();
        });
    });
}

async function uploadDirRecursive(sftp, localPath, remotePath) {
    const stats = fs.statSync(localPath);
    const normalizedRemote = remotePath.replace(/\\/g, '/');
    
    if (stats.isDirectory()) {
        await new Promise((resolve) => {
            sftp.mkdir(normalizedRemote, (err) => {
                // If it exists or fails, we still proceed to try and upload files
                resolve(); 
            });
        });

        const files = fs.readdirSync(localPath);
        for (const file of files) {
            await uploadDirRecursive(sftp, path.join(localPath, file), path.join(remotePath, file));
        }
    } else {
        await uploadFile(sftp, localPath, remotePath);
    }
}

conn.on('ready', () => {
    console.log('Syncing files to production...');
    conn.sftp(async (err, sftp) => {
        if (err) {
            console.error('SFTP Error:', err);
            conn.end();
            return;
        }

        try {
            // Ensure the remote driver-app folder exists in public/
            console.log('Ensuring remote public/driver-app exists...');
            await new Promise((resolve) => {
                sftp.mkdir(BASE_REMOTE, (err) => {
                    resolve(); 
                });
            });

            const files = fs.readdirSync(BASE_LOCAL);
            console.log(`Found ${files.length} items in local dist folder.`);
            for (const file of files) {
                console.log(`-> Syncing: ${file}`);
                await uploadDirRecursive(sftp, path.join(BASE_LOCAL, file), path.join(BASE_REMOTE, file));
            }
            
            // Sync DriverAppController.php as well
            console.log('Syncing DriverAppController.php...');
            await new Promise((resolve, reject) => {
                sftp.fastPut(
                    path.join(__dirname, 'app/Http/Controllers/Api/DriverAppController.php'),
                    '/home/u747826271/domains/eurotaxisystem.site/public_html/app/Http/Controllers/Api/DriverAppController.php',
                    (err) => err ? reject(err) : resolve()
                );
            });

            console.log('Syncing routes/api.php...');
            await new Promise((resolve, reject) => {
                sftp.fastPut(
                    path.join(__dirname, 'routes/api.php'),
                    '/home/u747826271/domains/eurotaxisystem.site/public_html/routes/api.php',
                    (err) => err ? reject(err) : resolve()
                );
            });

            console.log('Syncing routes/web.php...');
            await new Promise((resolve, reject) => {
                sftp.fastPut(
                    path.join(__dirname, 'routes/web.php'),
                    '/home/u747826271/domains/eurotaxisystem.site/public_html/routes/web.php',
                    (err) => err ? reject(err) : resolve()
                );
            });

            console.log('Syncing StaffController.php...');
            await new Promise((resolve, reject) => {
                sftp.fastPut(
                    path.join(__dirname, 'app/Http/Controllers/StaffController.php'),
                    '/home/u747826271/domains/eurotaxisystem.site/public_html/app/Http/Controllers/StaffController.php',
                    (err) => err ? reject(err) : resolve()
                );
            });

            console.log('Syncing resources/views/staff/index.blade.php...');
            await new Promise((resolve, reject) => {
                sftp.fastPut(
                    path.join(__dirname, 'resources/views/staff/index.blade.php'),
                    '/home/u747826271/domains/eurotaxisystem.site/public_html/resources/views/staff/index.blade.php',
                    (err) => err ? reject(err) : resolve()
                );
            });

            console.log('Syncing ArchiveController.php...');
            await new Promise((resolve, reject) => {
                sftp.fastPut(
                    path.join(__dirname, 'app/Http/Controllers/Api/ArchiveController.php'),
                    '/home/u747826271/domains/eurotaxisystem.site/public_html/app/Http/Controllers/Api/ArchiveController.php',
                    (err) => err ? reject(err) : resolve()
                );
            });

            console.log('Syncing Web ArchiveController.php...');
            await new Promise((resolve, reject) => {
                sftp.fastPut(
                    path.join(__dirname, 'app/Http/Controllers/ArchiveController.php'),
                    '/home/u747826271/domains/eurotaxisystem.site/public_html/app/Http/Controllers/ArchiveController.php',
                    (err) => err ? reject(err) : resolve()
                );
            });

            console.log('Syncing SupportController.php...');
            await new Promise((resolve, reject) => {
                sftp.fastPut(
                    path.join(__dirname, 'app/Http/Controllers/Api/SupportController.php'),
                    '/home/u747826271/domains/eurotaxisystem.site/public_html/app/Http/Controllers/Api/SupportController.php',
                    (err) => err ? reject(err) : resolve()
                );
            });

            console.log('Syncing SupportTicket.php model...');
            await new Promise((resolve, reject) => {
                sftp.fastPut(
                    path.join(__dirname, 'app/Models/SupportTicket.php'),
                    '/home/u747826271/domains/eurotaxisystem.site/public_html/app/Models/SupportTicket.php',
                    (err) => err ? reject(err) : resolve()
                );
            });

            console.log('Syncing SupportMessage.php model...');
            await new Promise((resolve, reject) => {
                sftp.fastPut(
                    path.join(__dirname, 'app/Models/SupportMessage.php'),
                    '/home/u747826271/domains/eurotaxisystem.site/public_html/app/Models/SupportMessage.php',
                    (err) => err ? reject(err) : resolve()
                );
            });

            console.log('Syncing AuthController.php...');
            await new Promise((resolve, reject) => {
                sftp.fastPut(
                    path.join(__dirname, 'app/Http/Controllers/Api/AuthController.php'),
                    '/home/u747826271/domains/eurotaxisystem.site/public_html/app/Http/Controllers/Api/AuthController.php',
                    (err) => err ? reject(err) : resolve()
                );
            });

            console.log('Syncing SemaphoreHelper.php...');
            await new Promise((resolve, reject) => {
                sftp.fastPut(
                    path.join(__dirname, 'app/Helpers/SemaphoreHelper.php'),
                    '/home/u747826271/domains/eurotaxisystem.site/public_html/app/Helpers/SemaphoreHelper.php',
                    (err) => err ? reject(err) : resolve()
                );
            });

            console.log('Syncing FirebasePushService.php...');
            await new Promise((resolve, reject) => {
                sftp.fastPut(
                    path.join(__dirname, 'app/Services/FirebasePushService.php'),
                    '/home/u747826271/domains/eurotaxisystem.site/public_html/app/Services/FirebasePushService.php',
                    (err) => err ? reject(err) : resolve()
                );
            });

            console.log('Syncing SuperAdminController.php...');
            await new Promise((resolve, reject) => {
                sftp.fastPut(
                    path.join(__dirname, 'app/Http/Controllers/SuperAdminController.php'),
                    '/home/u747826271/domains/eurotaxisystem.site/public_html/app/Http/Controllers/SuperAdminController.php',
                    (err) => err ? reject(err) : resolve()
                );
            });

            console.log('Syncing layouts/app.blade.php...');
            await new Promise((resolve, reject) => {
                sftp.fastPut(
                    path.join(__dirname, 'resources/views/layouts/app.blade.php'),
                    '/home/u747826271/domains/eurotaxisystem.site/public_html/resources/views/layouts/app.blade.php',
                    (err) => err ? reject(err) : resolve()
                );
            });

            console.log('Syncing SupportManagementController.php...');
            await new Promise((resolve, reject) => {
                sftp.fastPut(
                    path.join(__dirname, 'app/Http/Controllers/SupportManagementController.php'),
                    '/home/u747826271/domains/eurotaxisystem.site/public_html/app/Http/Controllers/SupportManagementController.php',
                    (err) => err ? reject(err) : resolve()
                );
            });

            console.log('Syncing Support Views...');
            await new Promise((resolve) => {
                sftp.mkdir('/home/u747826271/domains/eurotaxisystem.site/public_html/resources/views/support', (err) => resolve());
            });
            await new Promise((resolve, reject) => {
                sftp.fastPut(
                    path.join(__dirname, 'resources/views/support/index.blade.php'),
                    '/home/u747826271/domains/eurotaxisystem.site/public_html/resources/views/support/index.blade.php',
                    (err) => err ? reject(err) : resolve()
                );
            });
            await new Promise((resolve, reject) => {
                sftp.fastPut(
                    path.join(__dirname, 'resources/views/support/show.blade.php'),
                    '/home/u747826271/domains/eurotaxisystem.site/public_html/resources/views/support/show.blade.php',
                    (err) => err ? reject(err) : resolve()
                );
            });

            console.log('Parity sync complete.');
            
            // Clear caches on production
            console.log('Clearing production route & view cache...');
            conn.exec('cd /home/u747826271/domains/eurotaxisystem.site/public_html && php artisan route:clear && php artisan view:clear', (err, stream) => {
                if (err) {
                    console.error('Command Error:', err);
                    conn.end();
                    return;
                }
                stream.on('close', (code, signal) => {
                    console.log('Cache cleared successfully.');
                    conn.end();
                }).on('data', (data) => {
                    console.log('STDOUT: ' + data);
                }).stderr.on('data', (data) => {
                    console.log('STDERR: ' + data);
                });
            });
        } catch (e) {
            console.error('Deployment failed:', e);
            conn.end();
        }
    });
}).connect(config);
