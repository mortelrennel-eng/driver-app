
$files = @(
    "app/Http/Controllers/BoundaryController.php",
    "app/Http/Controllers/BoundarySettingsController.php",
    "app/Http/Controllers/CodingController.php",
    "app/Models/Boundary.php",
    "app/Models/BoundaryRule.php",
    "app/Models/CodingRecord.php",
    "app/Models/CodingViolation.php",
    "app/Traits/CalculatesBoundary.php",
    "resources/views/boundaries/index.blade.php",
    "resources/views/settings/boundary-rules.blade.php",
    "resources/views/coding/index.blade.php",
    "resources/views/coding/violations.blade.php",
    "routes/web.php"
)

$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$backupDir = "C:\xampp\htdocs\eurotaxisystem\backups\boundaries_full_sync_$timestamp"

if (!(Test-Path $backupDir)) {
    New-Item -ItemType Directory -Path $backupDir
}

foreach ($file in $files) {
    $sourcePath = "C:\xampp\htdocs\eurotaxisystem\$file"
    if (Test-Path $sourcePath) {
        $destPath = Join-Path $backupDir ([System.IO.Path]::GetFileName($file))
        Copy-Item $sourcePath $destPath
        Write-Host "Backed up $file to $destPath"
    } else {
        Write-Host "File $file not found, skipping backup."
    }
}
