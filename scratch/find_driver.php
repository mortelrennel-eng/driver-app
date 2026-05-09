<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$phone = '09911275418';
$cleanPhone = substr(preg_replace('/[^0-9]/', '', $phone), -10);

$drivers = \App\Models\Driver::where('contact_number', 'LIKE', '%' . $cleanPhone)->get();

if ($drivers->count() > 0) {
    foreach ($drivers as $d) {
        echo json_encode([
            'id' => $d->id,
            'first_name' => $d->first_name,
            'last_name' => $d->last_name,
            'contact_number' => $d->contact_number,
            'user_id' => $d->user_id
        ]) . "\n";
    }
} else {
    echo "No driver found for phone $cleanPhone";
}
