const Client = require('ssh2-sftp-client');
const config = { host: '195.35.62.133', port: 65002, username: 'u747826271', password: '@Admineuro2026', readyTimeout: 30000 };
const sftp = new Client();
sftp.connect(config).then(() => sftp.get('/home/u747826271/domains/eurotaxisystem.site/public_html/routes/web.php')).then(content => { 
  const text = content.toString();
  const marker = "name('dashboard')";
  const idx = text.indexOf(marker);
  if (idx === -1) { 
    console.log('ERROR: dashboard route name NOT FOUND on server!');
    // Show total line count
    console.log('Total chars on server file: ' + text.length);
    console.log('Total lines: ' + text.split('\n').length);
  } else { 
    console.log('Found dashboard route name at char: ' + idx);
    console.log(text.substring(idx-80, idx+80));
  }
  sftp.end();
}).catch(e => { console.error(e.message); sftp.end(); });
