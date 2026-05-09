<?php
$files = [
    'app/Http/Controllers/DriverManagementController.php',
    'app/Models/DriverBehavior.php',
    'app/Services/NotificationService.php',
    'resources/views/driver-behavior/index.blade.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $content = str_replace("'Coding Violation', ", "", $content);
        $content = str_replace("'Coding Violation'    => 'bg-red-100 text-red-700 border-red-200',", "", $content);
        $content = str_replace("->where('db.incident_type', '!=', 'Coding Violation')", "", $content);
        file_put_contents($file, $content);
    }
}
echo "Done";
