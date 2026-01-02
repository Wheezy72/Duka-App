@echo off
setlocal ENABLEDELAYEDEXPANSION

REM ============================================================
REM Duka-App bootstrap script (Windows, composer required)
REM
REM Usage:
REM   1. Create a folder named duka-app
REM   2. Clone or extract THIS repo into that folder so that
REM      setup_duka_app.bat sits at duka-app\setup_duka_app.bat
REM   3. Run this script from that folder:
REM         setup_duka_app.bat
REM
REM The script will:
REM   - Create a fresh Laravel app using composer
REM   - Copy the Duka-App module files into it
REM   - Move the Laravel app up one level so duka-app is a full app
REM ============================================================

set "BASE_DIR=%~dp0"
cd /d "%BASE_DIR%"

echo.
echo [1/4] Creating fresh Laravel application in temp-laravel-app...
echo.

composer create-project laravel/laravel temp-laravel-app
if errorlevel 1 (
    echo Composer failed. Make sure composer is installed and on PATH.
    goto :EOF
)

echo.
echo [2/4] Copying Duka-App module files into temp-laravel-app...
echo.

REM Copy app code (models, services, console, etc.)
xcopy app temp-laravel-app\app /E /Y >nul

REM Copy database migrations and seeders
xcopy database temp-laravel-app\database /E /Y >nul

REM Copy resources (views, layouts, POS screens, settings, etc.)
xcopy resources temp-laravel-app\resources /E /Y >nul

REM Merge routes/web.php (this will overwrite temp-laravel-app\routes\web.php)
copy /Y routes\web.php temp-laravel-app\routes\web.php >nul

REM Copy env example and helper scripts
copy /Y .env.example temp-laravel-app\.env.example >nul
copy /Y dev_run.bat temp-laravel-app\dev_run.bat >nul
copy /Y launcher.bat temp-laravel-app\launcher.bat >nul
copy /Y dev_guide.md temp-laravel-app\dev_guide.md >nul

echo.
echo [3/4] Moving Laravel app up one level...
echo.

xcopy temp-laravel-app\* . /E /Y >nul

echo.
echo [4/4] Cleaning up temp-laravel-app folder...
echo.

rmdir /S /Q temp-laravel-app

echo.
echo Done.
echo.
echo Next steps:
echo   1. Create .env from .env.example and fill in DB and API keys.
echo   2. Run:  php artisan key:generate
echo   3. Run:  php artisan migrate --seed
echo   4. For development, run: dev_run.bat
echo.

endlocal
exit /b 0