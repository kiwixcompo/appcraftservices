@echo off
title App Craft Services - Quick Sync
color 0A

echo.
echo  ╔══════════════════════════════════════════════════════════════╗
echo  ║                    APP CRAFT SERVICES                        ║
echo  ║                      QUICK SYNC TOOL                         ║
echo  ╚══════════════════════════════════════════════════════════════╝
echo.
echo  Repository: https://github.com/kiwixcompo/appcraftservices
echo  ══════════════════════════════════════════════════════════════
echo.

cd /d "%~dp0"

REM Quick repository setup check
git remote get-url origin >nul 2>&1
if errorlevel 1 (
    echo  [SETUP] Connecting to repository...
    git remote add origin https://github.com/kiwixcompo/appcraftservices.git
    git branch -M main
)

echo  [SYNC] Pulling latest changes...
git pull origin main --no-edit >nul 2>&1

echo  [SYNC] Adding your changes...
git add .

git diff --cached --quiet
if errorlevel 1 (
    REM Get timestamp
    for /f "tokens=2 delims==" %%a in ('wmic OS Get localdatetime /value') do set "dt=%%a"
    set "timestamp=%dt:~0,4%-%dt:~4,2%-%dt:~6,2% %dt:~8,2%:%dt:~10,2%"
    
    echo  [SYNC] Committing changes...
    git commit -m "Auto-sync: !timestamp!" >nul
    
    echo  [SYNC] Uploading to GitHub...
    git push origin main >nul 2>&1
    
    if errorlevel 1 (
        echo.
        echo  ❌ SYNC FAILED - Check your internet connection or GitHub access
        echo.
        timeout /t 5 >nul
        exit /b 1
    )
    
    echo.
    echo  ✅ SUCCESS! Your changes are now live on GitHub
    echo.
    echo  View at: https://github.com/kiwixcompo/appcraftservices
    echo.
) else (
    echo.
    echo  ℹ️  No changes detected - Repository is up to date!
    echo.
)

echo  Press any key to close...
pause >nul