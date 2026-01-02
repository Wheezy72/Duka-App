@echo off
setlocal

REM Base directory of this script (where Duka-App bundle lives)
set "BASE_DIR=%~dp0"

REM Path to Laravel app and PHP runtime (adjust if your layout is different)
set "DUKA_APP_DIR=%BASE_DIR%duka-app"
set "PHP_EXE=%BASE_DIR%php\php.exe"

REM Change to Laravel app directory
cd /d "%DUKA_APP_DIR%"

REM Environment for Laravel when running as a desktop POS
set APP_ENV=local
set APP_DEBUG=false
set APP_URL=http://127.0.0.1:8080

REM Start PHP built-in server in a new window so this script can continue
start "Duka-PHP-Server" "%PHP_EXE%" -S 127.0.0.1:8080 -t public

REM Wait a bit for the server to boot
timeout /t 3 /nobreak >nul

REM Launch the Nativefier desktop app if present
if exist "%BASE_DIR%desktop\Duka-App.exe" (
    cd /d "%BASE_DIR%desktop"
    start "" "Duka-App.exe"
) else (
    REM Fallback: open POS in default browser
    start "" "http://127.0.0.1:8080"
)

endlocal
exit /b