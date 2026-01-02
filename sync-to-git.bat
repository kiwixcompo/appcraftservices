@echo off
echo ========================================
echo   App Craft Services - Auto Git Sync
echo ========================================
echo Repository: https://github.com/kiwixcompo/appcraftservices
echo.

REM Change to the project directory
cd /d "%~dp0"

echo Current directory: %CD%
echo.

REM Check if git is available
git --version >nul 2>&1
if errorlevel 1 (
    echo ERROR: Git is not installed or not in PATH
    echo Please install Git and try again
    pause
    exit /b 1
)

REM Ensure we're connected to the correct repository
echo Verifying repository connection...
git remote get-url origin >nul 2>&1
if errorlevel 1 (
    echo Setting up repository connection...
    git remote add origin https://github.com/kiwixcompo/appcraftservices.git
    git branch -M main
) else (
    REM Check if the URL is correct
    for /f "delims=" %%i in ('git remote get-url origin') do set "current_url=%%i"
    if not "!current_url!"=="https://github.com/kiwixcompo/appcraftservices.git" (
        echo Updating repository URL...
        git remote set-url origin https://github.com/kiwixcompo/appcraftservices.git
    )
)

REM Pull latest changes first to avoid conflicts
echo Pulling latest changes...
git pull origin main --no-edit >nul 2>&1

REM Add all changes
echo Adding all changes to Git...
git add .

REM Check if there are any changes to commit
git diff --cached --quiet
if errorlevel 1 (
    echo Changes detected. Creating commit...
    
    REM Get current date and time for commit message
    for /f "tokens=2 delims==" %%a in ('wmic OS Get localdatetime /value') do set "dt=%%a"
    set "YY=%dt:~2,2%" & set "YYYY=%dt:~0,4%" & set "MM=%dt:~4,2%" & set "DD=%dt:~6,2%"
    set "HH=%dt:~8,2%" & set "Min=%dt:~10,2%" & set "Sec=%dt:~12,2%"
    set "timestamp=%YYYY%-%MM%-%DD% %HH%:%Min%:%Sec%"
    
    REM Commit with timestamp
    git commit -m "Auto-sync: %timestamp%"
    
    if errorlevel 1 (
        echo ERROR: Failed to create commit
        pause
        exit /b 1
    )
    
    echo Commit created successfully!
    
    REM Push to the specific repository
    echo Pushing to https://github.com/kiwixcompo/appcraftservices...
    git push origin main
    
    if errorlevel 1 (
        echo.
        echo WARNING: Failed to push to remote repository
        echo This might be because:
        echo 1. You don't have internet connection
        echo 2. Authentication failed (you may need to login to GitHub)
        echo 3. Repository access permissions
        echo.
        echo Your changes are committed locally but not pushed to remote.
        echo Try running the sync again when you have internet access.
        pause
        exit /b 1
    )
    
    echo.
    echo ========================================
    echo   Sync completed successfully!
    echo ========================================
    echo.
    echo Your changes have been:
    echo ✓ Added to Git
    echo ✓ Committed locally  
    echo ✓ Pushed to https://github.com/kiwixcompo/appcraftservices
    echo.
    
) else (
    echo No changes detected to commit.
    echo Repository is up to date!
    echo.
)

REM Show recent commits
echo Recent commits:
git log --oneline -5

echo.
echo Sync complete! You can view your changes at:
echo https://github.com/kiwixcompo/appcraftservices
echo.
echo Press any key to close...
pause >nul