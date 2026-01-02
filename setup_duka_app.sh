#!/usr/bin/env bash
set -euo pipefail

# ============================================================
# Duka-App bootstrap script (Unix/macOS, composer required)
#
# Usage:
#   1. mkdir duka-app && cd duka-app
#   2. Clone or extract THIS repo into that folder so that
#      setup_duka_app.sh sits at duka-app/setup_duka_app.sh
#   3. Run:
#        chmod +x setup_duka_app.sh
#        ./setup_duka_app.sh
#
# The script will:
#   - Create a fresh Laravel app using composer
#   - Copy the Duka-App module files into it
#   - Move the Laravel app up one level so duka-app is a full app
# ============================================================

BASE_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$BASE_DIR"

echo
echo "[1/4] Creating fresh Laravel application in temp-laravel-app..."
echo

composer create-project laravel/laravel temp-laravel-app

echo
echo "[2/4] Copying Duka-App module files into temp-laravel-app..."
echo

# Copy app code
rsync -a app/ temp-laravel-app/app/

# Copy database migrations and seeders
rsync -a database/ temp-laravel-app/database/

# Copy resources (views, layouts, POS screens, settings, etc.)
rsync -a resources/ temp-laravel-app/resources/

# Merge routes/web.php (overwrite)
cp -f routes/web.php temp-laravel-app/routes/web.php

# Copy env example and helper scripts
cp -f .env.example temp-laravel-app/.env.example
cp -f dev_run.bat temp-laravel-app/dev_run.bat
cp -f launcher.bat temp-laravel-app/launcher.bat
cp -f dev_guide.md temp-laravel-app/dev_guide.md

echo
echo "[3/4] Moving Laravel app up one level..."
echo

rsync -a temp-laravel-app/ ./

echo
echo "[4/4] Cleaning up temp-laravel-app folder..."
echo

rm -rf temp-laravel-app

echo
echo "Done."
echo
echo "Next steps:"
echo "  1. Create .env from .env.example and fill in DB and API keys."
echo "  2. Run:  php artisan key:generate"
echo "  3. Run:  php artisan migrate --seed"
echo "  4. For development, run: dev_run.bat (on Windows) or:"
echo "         php artisan serve & npm run dev"
echo