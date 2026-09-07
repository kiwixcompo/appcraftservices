@echo off
title App Craft Services - Auto-Deploy Sync
color 0A

REM Enable UTF-8 for displaying emojis correctly
chcp 65001 >nul

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

REM Check if there are new changes to commit
set "has_new_changes=0"
git diff --cached --quiet
if errorlevel 1 set "has_new_changes=1"

if "%has_new_changes%"=="1" (
    REM Get timestamp
    for /f "tokens=2 delims==" %%a in ('wmic OS Get localdatetime /value 2^>nul') do set "dt=%%a"
    if defined dt (
        set "timestamp=%dt:~0,4%-%dt:~4,2%-%dt:~6,2% %dt:~8,2%:%dt:~10,2%"
    ) else (
        set "timestamp=%date% %time%"
    )
    
    echo  [SYNC] Committing changes...
    git commit -m "Auto-sync: %timestamp%" >nul
)

REM Check if there are ANY unpushed commits (including the one we just made, or previous ones)
set "unpushed=0"
for /f "tokens=*" %%c in ('git rev-list --count origin/main..HEAD 2^>nul') do set "unpushed=%%c"

if not "%unpushed%"=="0" (
    echo  [SYNC] Uploading %unpushed% commit(s) to GitHub...
    git push origin main
    
    if errorlevel 1 (
        echo.
        echo  ❌ SYNC FAILED - Check your internet connection or GitHub access
        echo.
        timeout /t 5 >nul
        exit /b 1
    )
    
    echo.
    echo  ✅ SUCCESS! Changes uploaded to GitHub
    echo.
    echo  [DEPLOY] Triggering auto-deployment to live site...
    
    REM Trigger manual deployment
    curl -s "https://appcraftservices.com/deploy.php?manual=true" >nul 2>&1
    
    if errorlevel 1 (
        echo  ⚠️  Manual deployment trigger failed - webhook should handle it
    ) else (
        echo  ✅ Live site deployment triggered successfully!
    )
    
    echo.
    echo  🌐 Your changes will be live at: https://appcraftservices.com
    echo  💻 View repository: https://github.com/kiwixcompo/appcraftservices
    echo.
) else (
    echo.
    echo  ℹ️  No changes detected - Repository is up to date!
    echo.
)

echo  ══════════════════════════════════════════════════════════════
echo  Press any key to close...
pause >nul