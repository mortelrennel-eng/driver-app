const Client = require('ssh2-sftp-client');
const sftp = new Client();
const config = {
    host: '193.203.162.246',
    port: 22,
    username: 'u446869818',
    password: 'Password123!' // Using password from previous deploy scripts
};

const localFile = 'public/migrate_prod.php';
const remotePath = '/home/u446869818/domains/eurotaxisystem.site/public_html/public/migrate_prod.php';

async function main() {
    try {
        await sftp.connect(config);
        console.log('Connected via SFTP');
        await sftp.fastPut(localFile, remotePath);
        console.log('Uploaded migrate_prod.php to Hostinger');
    } catch (err) {
        console.error(err.message);
    } finally {
        sftp.end();
    }
}
main();
