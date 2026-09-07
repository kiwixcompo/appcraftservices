@echo off
title App Craft Services - Auto-Deploy Sync
color 0A

echo.
echo  ╔══════════════════════════════════════════════════════════════╗
echo  ║                    APP CRAFT SERVICES                        ║
echo  ║                   AUTO-DEPLOY SYNC TOOL                      ║
echo  ╚══════════════════════════════════════════════════════════════╝
echo.
echo  Repository: https://github.com/kiwixcompo/appcraftservices
echo  Live Site:  https://appcraftservices.com
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
    for /f "tokens=2 delims==" %%a in ('wmic OS Get localdatetime /value 2^>nul') do set "dt=%%a"
    if defined dt (
        set "timestamp=%dt:~0,4%-%dt:~4,2%-%dt:~6,2% %dt:~8,2%:%dt:~10,2%"
    ) else (
        set "timestamp=%date% %time%"
    )
    
    echo  [SYNC] Committing changes...
    git commit -m "Auto-sync: %timestamp%"
)

set "unpushed=0"
for /f "tokens=*" %%c in ('git rev-list --count origin/main..HEAD 2^>nul') do set "unpushed=%%c"

if not "%unpushed%"=="0" (
    echo.
    echo  [SYNC] Found %unpushed% commit(s) ready to push.
    echo  [SYNC] Uploading to GitHub (origin/main)...
    echo.
    git push origin main
    if errorlevel 1 (
        echo.
        echo  ❌ SYNC FAILED - Check your internet connection or GitHub access
        echo.
        echo  Press any key to close...
        pause >nul
        exit /b 1
    )
    echo.
    echo  ✅ SUCCESS! Changes uploaded to GitHub
    echo.
    echo  [DEPLOY] Triggering auto-deployment to live site...
    curl -s "https://appcraftservices.com/deploy.php?manual=true"
    echo.
    echo  ✅ Live site deployment triggered successfully!
    echo.
    echo  🌐 Your changes will be live at: https://appcraftservices.com
    echo  📊 View repository: https://github.com/kiwixcompo/appcraftservices
    echo.
) else (
    echo.
    echo  ℹ️  No changes detected - Repository is up to date!
    echo.
)

echo  Press any key to close...
pause >nul