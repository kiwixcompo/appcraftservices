@echo off
chcp 65001 >nul
title App Craft Services - Auto-Deploy Sync
color 0A

echo.
echo ==================================================================
echo                    APP CRAFT SERVICES                             
echo                   AUTO-DEPLOY SYNC TOOL                           
echo ==================================================================
echo.
echo Repository: https://github.com/kiwixcompo/appcraftservices
echo Live Site:  https://appcraftservices.com
echo ==================================================================
echo.

cd /d "%~dp0"

REM Quick repository setup check
git remote get-url origin >nul 2>&1
if errorlevel 1 (
    echo [SETUP] Connecting to repository...
    git remote add origin https://github.com/kiwixcompo/appcraftservices.git
    git branch -M main
)

echo [SYNC] Pulling latest changes from GitHub...
git pull origin main --no-edit
if errorlevel 1 (
    echo [NOTICE] Pull encountered an issue or requires auth. Proceeding...
)

echo.
echo [SYNC] Adding changed files...
git add .

echo [SYNC] Checking for changes...
git diff --cached --quiet
if errorlevel 1 (
    for /f "tokens=2 delims==" %%a in ('wmic OS Get localdatetime /value 2^>nul') do set "dt=%%a"
    if defined dt (
        set "timestamp=%dt:~0,4%-%dt:~4,2%-%dt:~6,2% %dt:~8,2%:%dt:~10,2%"
    ) else (
        set "timestamp=%date% %time%"
    )
    
    echo [SYNC] Committing changes...
    git commit -m "Auto-sync: %timestamp%"
)

echo.
echo [SYNC] Checking for unpushed commits...
set "unpushed=0"
for /f "tokens=*" %%c in ('git rev-list --count @{u}..HEAD 2^>nul') do set "unpushed=%%c"

if "%unpushed%"=="0" (
    for /f "tokens=*" %%c in ('git rev-list --count origin/main..HEAD 2^>nul') do set "unpushed=%%c"
)

if not "%unpushed%"=="0" (
    echo [SYNC] Found %unpushed% unpushed commit(s).
    echo [SYNC] Uploading to GitHub (origin/main)...
    echo.
    git push origin main
    if errorlevel 1 (
        echo.
        echo ==================================================================
        echo   [ERROR] Push failed.
        echo   Please verify your internet connection or GitHub authentication.
        echo ==================================================================
        echo.
        echo Press any key to exit...
        pause >nul
        exit /b 1
    )
    echo.
    echo [SUCCESS] Changes uploaded to GitHub successfully!
    echo.
    echo [DEPLOY] Triggering auto-deployment on live server...
    curl -s "https://appcraftservices.com/deploy.php?manual=true"
    echo.
    echo [DEPLOY] Deployment trigger sent!
    echo.
    echo Your changes will be live at: https://appcraftservices.com
) else (
    echo [STATUS] No new commits to push. Everything is up to date!
)

echo.
echo ==================================================================
echo Press any key to close this window...
pause >nul