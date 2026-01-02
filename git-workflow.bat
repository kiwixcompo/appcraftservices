@echo off
setlocal enabledelayedexpansion

echo ========================================
echo   App Craft Services - Git Workflow
echo ========================================
echo Repository: https://github.com/kiwixcompo/appcraftservices
echo.

REM Change to the project directory
cd /d "%~dp0"

REM Ensure repository connection
git remote get-url origin >nul 2>&1
if errorlevel 1 (
    echo Setting up repository connection...
    git remote add origin https://github.com/kiwixcompo/appcraftservices.git
    git branch -M main
    echo Repository connected!
    echo.
)

:MENU
echo Choose an option:
echo 1. Quick Sync (add, commit, push)
echo 2. Sync with custom message
echo 3. Check status
echo 4. View recent commits
echo 5. Pull latest changes
echo 6. Create new branch
echo 7. Switch branch
echo 8. View repository online
echo 9. Exit
echo.
set /p choice="Enter your choice (1-9): "

if "%choice%"=="1" goto QUICK_SYNC
if "%choice%"=="2" goto CUSTOM_SYNC
if "%choice%"=="3" goto STATUS
if "%choice%"=="4" goto LOG
if "%choice%"=="5" goto PULL
if "%choice%"=="6" goto NEW_BRANCH
if "%choice%"=="7" goto SWITCH_BRANCH
if "%choice%"=="8" goto VIEW_ONLINE
if "%choice%"=="9" goto EXIT

echo Invalid choice. Please try again.
echo.
goto MENU

:QUICK_SYNC
echo.
echo Performing quick sync to https://github.com/kiwixcompo/appcraftservices...
git pull origin main --no-edit >nul 2>&1
git add .

REM Check if there are changes
git diff --cached --quiet
if errorlevel 1 (
    REM Get timestamp for commit message
    for /f "tokens=2 delims==" %%a in ('wmic OS Get localdatetime /value') do set "dt=%%a"
    set "YY=!dt:~2,2!" & set "YYYY=!dt:~0,4!" & set "MM=!dt:~4,2!" & set "DD=!dt:~6,2!"
    set "HH=!dt:~8,2!" & set "Min=!dt:~10,2!" & set "Sec=!dt:~12,2!"
    set "timestamp=!YYYY!-!MM!-!DD! !HH!:!Min!:!Sec!"
    
    git commit -m "Auto-sync: !timestamp!"
    git push origin main
    echo ✅ Sync completed successfully!
) else (
    echo ℹ️ No changes to commit.
)
echo.
pause
goto MENU

:CUSTOM_SYNC
echo.
set /p commit_msg="Enter commit message: "
if "%commit_msg%"=="" (
    echo No message provided. Cancelling.
    goto MENU
)

git pull origin main --no-edit >nul 2>&1
git add .
git commit -m "%commit_msg%"
git push origin main
echo ✅ Sync completed with message: %commit_msg%
echo.
pause
goto MENU

:STATUS
echo.
echo Git Status:
git status
echo.
pause
goto MENU

:LOG
echo.
echo Recent commits:
git log --oneline -10
echo.
pause
goto MENU

:PULL
echo.
echo Pulling latest changes from https://github.com/kiwixcompo/appcraftservices...
git pull origin main
echo.
pause
goto MENU

:NEW_BRANCH
echo.
set /p branch_name="Enter new branch name: "
if "%branch_name%"=="" (
    echo No branch name provided. Cancelling.
    goto MENU
)
git checkout -b %branch_name%
git push -u origin %branch_name%
echo ✅ Created and switched to branch: %branch_name%
echo.
pause
goto MENU

:SWITCH_BRANCH
echo.
echo Available branches:
git branch -a
echo.
set /p branch_name="Enter branch name to switch to: "
if "%branch_name%"=="" (
    echo No branch name provided. Cancelling.
    goto MENU
)
git checkout %branch_name%
echo.
pause
goto MENU

:VIEW_ONLINE
echo.
echo Opening repository in your default browser...
start https://github.com/kiwixcompo/appcraftservices
echo.
pause
goto MENU

:EXIT
echo.
echo Goodbye!
exit /b 0