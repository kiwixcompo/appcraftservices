@echo off
echo ========================================
echo   Setup Remote Git Repository
echo ========================================
echo.

REM Instructions for the user
echo INSTRUCTIONS:
echo 1. Create a new repository on GitHub
echo 2. Copy the repository URL (e.g., https://github.com/username/repo-name.git)
echo 3. Enter it below when prompted
echo.

set /p REPO_URL="Enter your GitHub repository URL: "

if "%REPO_URL%"=="" (
    echo ERROR: No URL provided
    pause
    exit /b 1
)

echo.
echo Setting up remote repository...
git remote add origin %REPO_URL%

if errorlevel 1 (
    echo Repository might already be configured. Updating...
    git remote set-url origin %REPO_URL%
)

echo.
echo Pushing initial commit to remote repository...
git branch -M main
git push -u origin main

if errorlevel 1 (
    echo.
    echo If you get authentication errors, you may need to:
    echo 1. Set up a Personal Access Token on GitHub
    echo 2. Use 'git config --global credential.helper manager-core'
    echo 3. Or use SSH keys for authentication
    echo.
)

echo.
echo Setup complete! You can now use sync-to-git.bat to automatically sync changes.
echo.
pause