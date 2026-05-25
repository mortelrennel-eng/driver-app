<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Announcement;

$count = Announcement::count();
$latest = Announcement::where('is_active', true)->orderBy('is_pinned', 'desc')->orderBy('created_at', 'desc')->first();

echo "Total Announcements: " . $count . "\n";
if ($latest) {
    echo "Latest Pinned: " . $latest->title . " (ID: " . $latest->id . ")\n";
} else {
    echo "No active announcements found.\n";
}
