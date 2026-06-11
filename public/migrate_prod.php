<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->handle(
    $input = new Symfony\Component\Console\Input\ArrayInput(['command' => 'migrate', '--force' => true]),
    new Symfony\Component\Console\Output\BufferedOutput()
);
echo "Migration completed on Hostinger.";
