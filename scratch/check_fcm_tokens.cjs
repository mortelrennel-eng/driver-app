const { Client } = require('ssh2');
const conn = new Client();

conn.on('ready', () => {
  const cmd = `mysql -u u747826271_eurotaxi -p'@Eurotaxi123' -h srv483.hstgr.io u747826271_eurotaxi -e "SELECT id, name, email, fcm_token FROM users WHERE fcm_token IS NOT NULL OR fcm_token != '';" 2>&1`;
  
  conn.exec(cmd, (err, stream) => {
    if(err){ console.error(err); conn.end(); return; }
    let out = '';
    stream.on('close', () => {
      console.log('FCM Tokens in DB:\n', out);
      conn.end();
    });
    stream.on('data', d => { out += d.toString(); });
    stream.stderr.on('data', d => process.stdout.write('[ERR]'+d.toString()));
  });
}).on('error', e => console.error('SSH ERR:', e))
  .connect({host:'195.35.62.133',port:65002,username:'u747826271',password:'@Admineuro2026',readyTimeout:30000});
