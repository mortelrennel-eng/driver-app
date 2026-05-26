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

echo [1/2] Pushing to GitHub (origin/main and sony/main)...
git add .
git commit -m "Deployment Update: %date% %time%"
git push origin main
git push sony main

echo.
echo [2/2] Syncing to Hostinger Server via SSH...
ssh -i "%KEY_PATH%" -p %SERVER_PORT% %SERVER_USER%@%SERVER_IP% "cd domains/eurotaxisystem.site/public_html && git fetch origin main && git reset --hard origin/main && php artisan migrate --force && php artisan optimize"

echo.
echo ===========================================
echo    DEPLOYMENT COMPLETE! Check your site.
echo ===========================================
