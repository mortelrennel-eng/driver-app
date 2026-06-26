<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$messages = \App\Models\SupportMessage::whereNotNull('attachment')->orderBy('id', 'desc')->take(3)->get(['id', 'attachment', 'message']);
print_r($messages->toArray());
