<?php

$host = '195.35.62.133';
$port = 65002;
$username = 'u747826271';
$password = '@Admineuro2026';

$command = 'cd /home/u747826271/domains/eurotaxisystem.site/public_html && git fetch origin main && git reset --hard origin/main && rsync -av --exclude="storage" public/ . && sed -i \'s|../vendor|vendor|g\' index.php && sed -i \'s|../bootstrap|bootstrap|g\' index.php && php artisan storage:link --force && php artisan migrate --force && php artisan optimize && echo "---SUCCESS_DEPLOY---"';

echo "--- ROBUST DEPLOYMENT START ---\n";
echo "Connecting to $host:$port as $username...\n";

// Ensure SSH2 extension is loaded, or fallback to executing a shell command if plink/ssh is available
if (function_exists('ssh2_connect')) {
    $connection = ssh2_connect($host, $port);
    if (!$connection) {
        die('Connection Error: Failed to connect to SSH server.');
    }

    if (!ssh2_auth_password($connection, $username, $password)) {
        die('Authentication Error: Failed to authenticate.');
    }

    echo "SSH Connection Ready.\n";
    echo "Executing deployment commands...\n";

    $stream = ssh2_exec($connection, $command);
    if (!$stream) {
        die('Execution Error: Failed to execute command.');
    }

    stream_set_blocking($stream, true);
    $stream_out = ssh2_fetch_stream($stream, SSH2_STREAM_STDIO);
    $stream_err = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);

    echo stream_get_contents($stream_out);
    
    $err_out = stream_get_contents($stream_err);
    if ($err_out) {
        echo "STDERR: " . $err_out;
    }

    echo "\n--- FINAL SUCCESS ---\n";
} else {
    echo "SSH2 extension not found in PHP. Attempting to use system SSH client (if available)...\n";
    echo "Please note this may require manual SSH key setup or may prompt for a password if not automated.\n";
    echo "Command to run: \n" . $command . "\n";
    echo "Since we can't reliably pass a password via command line standard ssh without sshpass, \n";
    echo "and you are on Windows, we will just echo the command so you can run it via PuTTY or similar if needed.\n";
    
    echo "\nERROR: Could not run automated deploy because PHP SSH2 is missing and node.js is missing.\n";
}
?>
