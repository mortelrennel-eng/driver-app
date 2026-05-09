
$files = @(
    "app/Http/Controllers/UnitController.php",
    "app/Models/Unit.php",
    "resources/views/units/index.blade.php",
    "resources/views/units/import.blade.php",
    "resources/views/units/print.blade.php",
    "resources/views/units/partials/unit_details_modal.blade.php",
    "resources/views/units/partials/_maintenance_health_bar.blade.php",
    "resources/views/units/partials/_units_grid.blade.php",
    "resources/views/units/partials/_units_table.blade.php"
)

$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$backupDir = "C:\xampp\htdocs\eurotaxisystem\backups\units_$timestamp"

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
