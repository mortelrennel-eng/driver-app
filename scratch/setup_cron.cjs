const { Client } = require('ssh2');
const conn = new Client();

const cronLine = '0 5 * * * curl -s "https://eurotaxisystem.site/api/cron/trigger-daily-coding?key=eurotaxi_secret_cron_2026" > /dev/null 2>&1';

conn.on('ready', () => {
    console.log('Connected to server. Setting up cron job...');
    
    // Command to check if the cron job already exists to prevent duplicates
    conn.exec('crontab -l', (err, stream) => {
        let currentCrontab = '';
        stream.on('data', (data) => { currentCrontab += data.toString(); });
        
        stream.on('close', () => {
            if (currentCrontab.includes('trigger-daily-coding')) {
                console.log('Cron job already exists. No changes made.');
                conn.end();
                return;
            }
            
            const newCrontab = currentCrontab + (currentCrontab.endsWith('\n') ? '' : '\n') + cronLine + '\n';
            
            conn.exec(`echo "${newCrontab.replace(/"/g, '\\"')}" | crontab -`, (err, writeStream) => {
                if (err) {
                    console.error('Error writing crontab:', err);
                }
                writeStream.on('close', () => {
                    console.log('Successfully added coding alert cron job!');
                    conn.end();
                });
            });
        });
    });
}).on('error', (err) => {
    console.error('Connection Error:', err.message);
}).connect({
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026'
});
