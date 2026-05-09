<?php
require 'vendor/autoload.php';
require_once 'app/Helpers/MailerHelper.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$testEmail = 'eurotaxisystem@gmail.com'; // Testing to your own email
echo "--- SMTP DIAGNOSTIC START ---\n";
echo "Attempting to send test email to: $testEmail\n";

$result = send_custom_email($testEmail, "Diagnostic Test - Euro Taxi System", "This is a test to check SMTP configuration.");

if ($result) {
    echo "SUCCESS: Email sent successfully!\n";
} else {
    echo "FAILED: Check storage/logs/laravel.log for the detailed SMTP error.\n";
}
echo "--- DIAGNOSTIC END ---\n";
