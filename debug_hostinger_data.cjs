const { Client } = require('ssh2');
const conn = new Client();
conn.on('ready', () => {
    const today = '2026-04-30';
    const queries = [
        `SELECT COUNT(*) as total FROM units WHERE deleted_at IS NULL`,
        `SELECT SUM(actual_boundary) as total FROM boundaries WHERE deleted_at IS NULL AND date = '${today}'`,
        `SELECT SUM(amount) as total FROM expenses WHERE deleted_at IS NULL AND date = '${today}'`,
        `SELECT SUM(total_salary) as total FROM salaries WHERE pay_date = '${today}'`,
        `SELECT COUNT(*) as total FROM drivers WHERE deleted_at IS NULL`,
        `SELECT COUNT(*) as total FROM maintenance WHERE deleted_at IS NULL AND LOWER(status) NOT IN ('complete', 'completed', 'cancelled')`
    ];
    
    let results = [];
    let count = 0;

    queries.forEach((q, i) => {
        const cmd = `mysql -u u747826271_eurotaxi -p@Eurotaxi123 u747826271_eurotaxi -e "${q};"`;
        conn.exec(cmd, (err, stream) => {
            stream.on('data', data => {
                const val = data.toString().split('\n')[1]?.trim() || '0';
                results[i] = val;
            }).on('close', () => {
                count++;
                if (count === queries.length) {
                    console.log('--- DB RESULTS ---');
                    console.log('Units:', results[0]);
                    console.log('Boundary:', results[1]);
                    console.log('Expenses:', results[2]);
                    console.log('Salaries:', results[3]);
                    console.log('Drivers:', results[4]);
                    console.log('Maintenance:', results[5]);
                    conn.end();
                }
            });
        });
    });
}).connect({host: '195.35.62.133', port: 65002, username: 'u747826271', password: '@Admineuro2026'});
