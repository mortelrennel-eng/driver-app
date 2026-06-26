const Client = require('ssh2-sftp-client');
const path = require('path');
const fs = require('fs');
const { execSync } = require('child_process');

const sftp = new Client();
const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026',
    retries: 3,
    retry_delay: 5000,
    readyTimeout: 30000,
};

const baseLocal = 'c:\\xampp\\htdocs\\eurotaxisystem-main\\';
const baseRemote = '/home/u747826271/domains/eurotaxisystem.site/public_html/';

const EXCLUDED_FILES = [
    'app/Http/Controllers/LiveTrackingController.php',
    'app/Services/TracksolidService.php',
    'public/js/realtime-tracking.js',
    'public/js/realtime-dashboard.js'
].map(f => f.replace(/\//g, '\\').toLowerCase());

async function upload() {
    try {
        console.log('Connecting via SFTP...');
        await sftp.connect(config);
        console.log('Connected!');

        // Get modified and untracked files
        const outMod = execSync('git ls-files -m', { encoding: 'utf8' });
        const outNew = execSync('git ls-files -o --exclude-standard', { encoding: 'utf8' });
        
        let files = [...outMod.split('\n'), ...outNew.split('\n')]
            .map(f => f.trim().replace(/\//g, '\\'))
            .filter(f => f.length > 0);

        // Filter
        files = files.filter(file => {
            if (file.includes('node_modules') || file.includes('.tempmediaStorage')) return false;
            
            // STRICT EXCLUSIONS
            for (const excluded of EXCLUDED_FILES) {
                if (file.toLowerCase() === excluded) {
                    console.log(`\n🚨 STRICTLY EXCLUDING: ${file} 🚨\n`);
                    return false;
                }
            }
            return true;
        });

        files = [...new Set(files)];
        console.log(`Found ${files.length} modified/new files to sync...`);

        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const localPath = path.join(baseLocal, file);
            
            if (!fs.existsSync(localPath)) continue;
            if (fs.statSync(localPath).isDirectory()) continue;

            const remotePath = baseRemote + file.replace(/\\/g, '/');
            const remoteDir = remotePath.substring(0, remotePath.lastIndexOf('/'));
            
            try {
                const dirExists = await sftp.exists(remoteDir);
                if (!dirExists) {
                    await sftp.mkdir(remoteDir, true);
                }
                await sftp.fastPut(localPath, remotePath);
                console.log(`Uploaded ${i + 1}/${files.length}: ${file}`);
            } catch (e) {
                console.error(`Failed to upload ${file}: ${e.message}`);
            }
        }

        console.log('✅ ALL SAFE FILES UPLOADED SUCCESSFULLY!');
    } catch (err) {
        console.error('❌ Error:', err.message);
    } finally {
        sftp.end();
    }
}

upload();
