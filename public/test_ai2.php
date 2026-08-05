<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());

$apiKey = config('services.gemini.api_key', env('GEMINI_API_KEY', ''));
echo "API Key Length: " . strlen($apiKey) . "\n";

if (empty($apiKey)) {
    echo "NO API KEY FOUND in .env\n";
} else {
    $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';
    $payload = json_encode([
        'contents' => [['parts' => [['text' => 'Hello, this is a test.']]]],
    ]);
    
    $ch = curl_init($endpoint . '?key=' . $apiKey);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    
    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "HTTP CODE: $httpCode\n";
    echo "ERROR: $error\n";
    echo "RESPONSE: $res\n";
}
