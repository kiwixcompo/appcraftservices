@echo off
REM Run this ONCE on your local machine to stop git tracking the data files.
REM This removes them from git's index (they stay on disk) so future pushes
REM won't overwrite them on the server.

echo Removing data files from git tracking...

git rm --cached data/messages.json 2>nul
git rm --cached data/blog_posts.json 2>nul
git rm --cached data/invoices.json 2>nul
git rm --cached data/projects.json 2>nul
git rm --cached data/payments.json 2>nul
git rm --cached data/contact_log.txt 2>nul
git rm --cached data/admin_log.txt 2>nul
git rm --cached data/editor_changes.log 2>nul
git rm --cached data/realtime_changes.json 2>nul
git rm --cached data/settings.json 2>nul
git rm --cached data/website_content.json 2>nul
git rm --cached -r data/backups/ 2>nul
git rm --cached -r assets/blog/ 2>nul

echo.
echo Committing the untrack changes...
git add .gitignore
git commit -m "chore: stop tracking persistent data files - they live only on server"

echo.
echo Done. From now on, git push will never overwrite messages, blog posts,
echo invoices, projects, or any other server-side data.
echo.
pause
