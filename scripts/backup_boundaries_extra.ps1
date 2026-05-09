
$files = @(
    "app/Http/Controllers/BoundarySettingsController.php",
    "app/Models/BoundaryRule.php"
)

$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$backupDir = "C:\xampp\htdocs\eurotaxisystem\backups\boundaries_extra_$timestamp"

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
