const { Client } = require('ssh2');
const conn = new Client();
conn.on('ready', () => {
    const cmd = `cd /home/u747826271/domains/eurotaxisystem.site/public_html && php -r "
        require 'vendor/autoload.php';
        \\$app = require 'bootstrap/app.php';
        \\$app->make(Illuminate\\Contracts\\Http\\Kernel::class);
        use Illuminate\\Support\\Facades\\DB;
        
        \\$todayDay = now()->timezone('Asia/Manila')->format('l');
        \\$allFleet = DB::table('units')->whereNull('deleted_at')->get();
        \\$count = \\$allFleet->filter(function(\\$unit) use (\\$todayDay) {
            return (\\$unit->coding_day ?: 'N/A') === \\$todayDay;
        })->count();
        echo 'CODING COUNT: ' . \\$count . PHP_EOL;
    "`;
    conn.exec(cmd, (err, stream) => {
        stream.on('data', data => console.log(data.toString()))
              .on('close', () => conn.end());
        stream.stderr.on('data', data => console.log('ERR: ' + data));
    });
}).connect({host: '195.35.62.133', port: 65002, username: 'u747826271', password: '@Admineuro2026'});
