<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Announcement;
use App\Models\User;

$ann = Announcement::create([
    'title' => 'System Update Test',
    'message' => 'This is a test announcement to verify the system is working correctly on the mobile app.',
    'type' => 'info',
    'is_pinned' => true,
    'is_active' => true,
    'created_by' => 1
]);

echo "Created Announcement ID: " . $ann->id . "\n";
echo "Pinned: " . ($ann->is_pinned ? 'Yes' : 'No') . "\n";
