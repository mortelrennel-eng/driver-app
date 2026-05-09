const { Client } = require('ssh2');
const conn = new Client();

conn.on('ready', () => {
  // Get FULL hash and verify
  const cmd = `mysql -u u747826271_eurotaxi -p'@Eurotaxi123' -h srv483.hstgr.io u747826271_eurotaxi -e "SELECT password_hash FROM users WHERE email='robertgarcia.owner@gmail.com';" 2>&1`;
  
  conn.exec(cmd, (err, stream) => {
    if(err){ console.error(err); conn.end(); return; }
    let out = '';
    stream.on('close', () => {
      // Extract the hash
      const lines = out.trim().split('\n');
      const hash = lines[lines.length-1].trim();
      console.log('Full hash:', hash);
      console.log('Is bcrypt:', hash.startsWith('$2y$') || hash.startsWith('$2b$'));
      
      // Now verify using PHP
      const phpCmd = `php -r "var_dump(password_verify('Admin@2026', '${hash}'));"`;
      conn.exec(phpCmd, (err2, stream2) => {
        if(err2){ conn.end(); return; }
        stream2.on('close', () => conn.end());
        stream2.on('data', d => process.stdout.write('PHP verify: '+d.toString()));
      });
    });
    stream.on('data', d => { out += d.toString(); });
    stream.stderr.on('data', d => process.stdout.write('[ERR]'+d.toString()));
  });
}).on('error', e => console.error('SSH ERR:', e))
  .connect({host:'195.35.62.133',port:65002,username:'u747826271',password:'@Admineuro2026',readyTimeout:30000});
