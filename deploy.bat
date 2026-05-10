@echo off
echo.
echo ===========================================
echo    EURO TAXI SYSTEM - SMART DEPLOYER
echo ===========================================
echo.

set KEY_PATH=%USERPROFILE%\.ssh\id_rsa_eurotaxi
set SERVER_USER=u747826271
set SERVER_IP=195.35.62.133
set SERVER_PORT=65002

echo [1/2] Pushing to GitHub (origin/main)...
git add .
git commit -m "Deployment Update: %date% %time%"
git push origin main

echo.
echo [2/2] Syncing to Hostinger Server via SSH...
ssh -i "%KEY_PATH%" -p %SERVER_PORT% %SERVER_USER%@%SERVER_IP% "cd domains/eurotaxisystem.site/public_html && git fetch origin main && git checkout origin/main -- app/Http/Controllers/MyAccountController.php resources/views/my-account/index.blade.php resources/views/layouts/app.blade.php routes/web.php app/Mail/EmailChangeRequested.php app/Mail/VerifyNewEmail.php resources/views/emails/email-change-requested.blade.php resources/views/emails/verify-new-email.blade.php database/migrations/2026_05_04_141234_add_email_change_fields_to_users_table.php app/Http/Controllers/AuthController.php app/Http/Controllers/Api/AuthController.php app/Http/Controllers/Api/DriverAppController.php && php artisan migrate --force && php artisan optimize"

echo.
echo ===========================================
echo    DEPLOYMENT COMPLETE! Check your site.
echo ===========================================
pause
