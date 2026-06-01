const { Client } = require('ssh2');
const fs = require('fs');

const config = { host: '195.35.62.133', port: 65002, username: 'u747826271', password: '@Admineuro2026', readyTimeout: 30000 };
const BASE_REMOTE = '/home/u747826271/domains/eurotaxisystem.site/public_html';

const conn = new Client();
conn.on('ready', () => {
  console.log('Zipping files on Hostinger...');
  // Zip app, resources, routes, database
  const cmd = 'cd ' + BASE_REMOTE + ' && zip -r sync_update.zip app resources routes database -x "*/node_modules/*" "*/vendor/*"';
  
  conn.exec(cmd, (err, stream) => {
    if (err) throw err;
    stream.on('close', () => {
      console.log('Zip created. Downloading...');
      conn.sftp((err, sftp) => {
        if (err) throw err;
        sftp.fastGet(BASE_REMOTE + '/sync_update.zip', 'scratch/sync_update.zip', (err) => {
          if (err) throw err;
          console.log('Download complete. Deleting remote zip...');
          conn.exec('rm ' + BASE_REMOTE + '/sync_update.zip', () => {
            conn.end();
          });
        });
      });
    }).on('data', d => {}).stderr.on('data', d => console.error(d.toString()));
  });
}).connect(config);
