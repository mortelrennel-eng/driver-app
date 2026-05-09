const { Client } = require('ssh2');

const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026',
    readyTimeout: 20000
};

const sql = `
CREATE TABLE IF NOT EXISTS login_audit (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    user_name VARCHAR(255) NULL,
    user_email VARCHAR(255) NULL,
    user_role VARCHAR(255) NULL,
    action VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    notes VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (user_id, action),
    INDEX (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
`;

const command = `cd /home/u747826271/domains/eurotaxisystem.site/public_html && php artisan tinker --execute="DB::statement('${sql.replace(/\n/g, ' ')}') " && echo "---FIX_SUCCESS---"`;

console.log('--- LOGIN AUDIT TABLE FIX START ---');
console.log(`Connecting to ${config.host}:${config.port}...`);

const conn = new Client();

conn.on('ready', () => {
    console.log('SSH Connection Ready.');
    conn.exec(command, (err, stream) => {
        if (err) {
            console.error('Execution Error:', err);
            conn.end();
            return;
        }
        stream.on('close', (code, signal) => {
            console.log(`\nStream Closed with code: ${code}`);
            conn.end();
        }).on('data', (data) => {
            process.stdout.write(`STDOUT: ${data}`);
        }).stderr.on('data', (data) => {
            process.stderr.write(`STDERR: ${data}`);
        });
    });
}).on('error', (err) => {
    console.error('Connection Error:', err);
}).connect(config);
