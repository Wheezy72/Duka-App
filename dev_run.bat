@echo off
setlocal

REM Simple dev script to run Laravel HTTP server and Vite in one shot.
REM Assumes this file lives at the Laravel project root.

set "BASE_DIR=%~dp0"
cd /d "%BASE_DIR%"

REM Ensure APP_ENV is local for development
set APP_ENV=local
set APP_DEBUG=true

REM Start Vite dev server (Tailwind/JS) in a new window
start "Duka-Vite" cmd /c "npm run dev"

REM Start Laravel HTTP server
php artisan serve --host=127.0.0.1 --port=8000

endlocal
exit /b