const { Client } = require('ssh2');

const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026',
};

const conn = new Client();

conn.on('ready', () => {
    console.log('SSH Ready. Checking paths...');
    
    // Check public_path() in Laravel and list files in uploads
    const cmd = "cd /home/u747826271/domains/eurotaxisystem.site/public_html && php artisan tinker --execute='echo \"Public Path: \" . public_path() . \"\\n\"; echo \"Uploads exist: \" . (is_dir(public_path(\"uploads/support_attachments\")) ? \"YES\" : \"NO\") . \"\\n\"; print_r(glob(public_path(\"uploads/support_attachments/*\")));'";

    conn.exec(cmd, (err, stream) => {
        if (err) throw err;
        stream.on('close', () => conn.end())
              .on('data', (data) => process.stdout.write(data))
              .stderr.on('data', (data) => process.stderr.write(data));
    });
}).connect(config);
