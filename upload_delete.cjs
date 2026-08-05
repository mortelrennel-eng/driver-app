const Client = require('ssh2-sftp-client');
const path = require('path');
const https = require('https');

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

const localFile = 'C:\\Users\\renne\\.gemini\\antigravity-ide\\brain\\8e38f971-1b97-4de3-a69c-4d2a62c2b77c\\scratch\\delete_test.php';
const remoteFile = '/home/u747826271/domains/eurotaxisystem.site/public_html/public/delete_test.php';

async function run() {
    try {
        console.log('Connecting...');
        await sftp.connect(config);
        console.log('Uploading script...');
        await sftp.fastPut(localFile, remoteFile);
        
        console.log('Executing script via curl...');
        https.get('https://eurotaxisystem.site/delete_test.php', (res) => {
            let data = '';
            res.on('data', (chunk) => {
                data += chunk;
            });
            res.on('end', async () => {
                console.log('API Response:', data);
                
                console.log('Cleaning up remote script...');
                await sftp.delete(remoteFile);
                sftp.end();
                console.log('Done.');
            });
        }).on('error', (err) => {
            console.error('HTTPS error:', err.message);
            sftp.end();
        });

    } catch (err) {
        console.error('Error:', err.message);
        sftp.end();
    }
}

run();
