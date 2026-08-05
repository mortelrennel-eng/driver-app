<?php
$file = __DIR__.'/../app/Http/Controllers/ChatController.php';
if (file_exists($file)) {
    echo file_get_contents($file);
} else {
    echo "File not found.";
}
