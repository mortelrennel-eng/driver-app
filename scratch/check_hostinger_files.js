const { Client } = require('ssh2');

const config = { host: '195.35.62.133', port: 65002, username: 'u747826271', password: '@Admineuro2026', readyTimeout: 30000 };
const BASE_REMOTE = '/home/u747826271/domains/eurotaxisystem.site/public_html';

const conn = new Client();
conn.on('ready', () => {
  const cmd = 'find ' + BASE_REMOTE + ' -type f -mtime -3 ! -path "*/storage/*" ! -path "*/vendor/*" ! -path "*/bootstrap/cache/*" ! -path "*/.git/*" ! -path "*/node_modules/*" ! -path "*/.npm/*"';
  
  conn.exec(cmd, (err, stream) => {
    if (err) { console.error(err); conn.end(); return; }
    let output = '';
    stream.on('close', (code) => { 
      console.log('--- RECENT FILES IN HOSTINGER ---');
      console.log(output);
      conn.end(); 
    }).on('data', d => { output += d; }).stderr.on('data', d => { console.error(d.toString()); });
  });
}).on('error', e => console.error('SSH Error:', e.message)).connect(config);
