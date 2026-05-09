@echo off
echo ========================================
echo Euro Taxi System - GitHub Deployment
echo ========================================
echo.

REM Check if we're in the right directory
if not exist ".git" (
    echo ERROR: Not a Git repository. Please run this from the project root.
    pause
    exit /b 1
)

REM Check if Git is available
git --version >nul 2>&1
if errorlevel 1 (
    echo ERROR: Git is not installed or not in PATH
    echo Please install Git from: https://git-scm.com/download/win
    pause
    exit /b 1
)

echo Checking current Git status...
git status

echo.
echo Adding all changes to staging...
git add .

echo.
echo Committing changes...
set /p commit_msg="Enter commit message (or press Enter for default): "
if "%commit_msg%"=="" set commit_msg=Auto deployment update - %date% %time%
git commit -m "%commit_msg%"

echo.
echo Pushing to GitHub...
git push origin main

echo.
if errorlevel 1 (
    echo ERROR: Failed to push to GitHub
    echo Please check your internet connection and GitHub credentials
    pause
    exit /b 1
) else (
    echo SUCCESS: All changes pushed to GitHub!
)

echo.
echo ========================================
echo Deployment completed successfully!
echo ========================================
pause
