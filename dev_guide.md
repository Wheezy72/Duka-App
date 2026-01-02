# Duka-App Dev Guide

This document explains what this repository contains, how to run it as a Laravel module, and how it fits into a desktop POS bundle with PHP and Nativefier.

## 1. What this is

Duka-App is a **Midnight Glass** themed, offline-first POS feature set for Kenyan dukas.

Right now, this repository is **a Laravel-style module**, not a complete Laravel application:

- There is **no `composer.json`, no `artisan`, no `vendor/`**, and no `config/` directory.
- The directory structure and code (routes, models, views, services) are written exactly as they would be inside a Laravel 11 app.
- You are expected to create a full Laravel project and then drop these files in (see section 6).

Key ideas:

- **Complexity in the backend, simplicity in the frontend**.
- Dark-mode POS terminal with glassmorphism and micro-interactions.
- SQLite-friendly and suitable for bundling with a portable PHP runtime and Nativefier into a desktop app.

## 2. Branding and icon guidelines

Duka-App should feel like a premium, modern POS at first glance. When you generate or commission the app icon, use this as a design brief:

- Shape: rounded square app icon (iOS/macOS style), soft corners.
- Background: deep navy → purple gradient, matching the app (`#0f172a` to `#1e1b4b`), with a subtle glass sheen.
- Foreground symbol:
  - A minimal shopping basket, POS terminal, or storefront awning in neon cyan/emerald, or
  - A compact “DA” monogram representing Duka-App.
- Colors:
  - Background: dark blue/purple.
  - Lines and highlights: neon cyan (`#22d3ee` / `#0ea5e9`) with small emerald accents (`#22c55e`).
- Style:
  - Glassmorphism hints: faint inner glow, soft highlights, slightly translucent layers.
  - Clean geometry, no generic clipart.
  - Readable at 64×64, 128×128, 512×512.
- Usage:
  - Desktop app icon (Nativefier `--icon`).
  - Sidebar badge (replace the “DA” square).
  - Auth card badge.
  - Favicon / PWA icons.
  
Once you choose a final icon, export:
- `.ico` for the Windows app/shortcuts.
- SVG/PNG for Blade views (sidebar, login, PDF headers).

## 3. Main features

### 2.1 Data model and migrations

Tables:

- `users` – extended for Google auth and roles.
- `products` – SKU, barcode, loose items (`is_loose`), `stock_quantity` (10,3), `price`.
- `customers` – name, phone, `total_debt` (10,2).
- `sales` – `user_id`, `customer_id`, `payment_method`, `payment_status`, `total_amount`.
- `sale_items` – `sale_id`, `product_id`, `quantity` (10,3), `price`, `subtotal`.

Models:

- `App\Models\User`
- `App\Models\Product`
- `App\Models\Customer`
- `App\Models\Sale`
- `App\Models\SaleItem`

### 2.2 POS Service

`App\Services\PosService`:

- `calculateQuantityFromMoney(Product $product, $amount): float`  
  Money mode for loose items (Kupima).
- `createSale(User $user, ?Customer $customer, string $paymentMethod, array $items, bool $markAsPaid = true): Sale`  
  Handles:
  - Normalizing items.
  - Creating `Sale` and `SaleItem` rows.
  - Decrementing inventory atomically.
  - Updating `customers.total_debt` for credit sales.

## 3. Main features

### 3.1 Data model and migrations

`resources/views/layouts/app.blade.php`:

- Dark gradient background: `from-[#0f172a] via-[#1e1b4b] to-[#0f172a]`.
- Glass panels via custom `.glass-panel` utility.
- Sidebar:
  - Dashboard, POS, (placeholder) Inventory, Debts.
  - Settings section with:
    - Backup & Restore.
    - System Logs.
- Mobile:
  - Top bar + bottom navigation.
- Toast notifications:
  - Slide-in from top-right using Alpine transitions.

### 2.4 POS Terminal

`resources/views/pos/terminal.blade.php`:

- Split layout:
  - Left: product search, speed dial items, product grid.
  - Right: cart and checkout.
- Search:
  - Keyboard/barcode input (Enter to add when single match).
- Speed dial:
  - Quick tap buttons for high-volume items (e.g., Bread, Milk, Airtime).
- Kupima:
  - Modal for loose items with 1/4, 1/2, 1 KG shortcuts.
  - Money mode: amount in KES → fractional quantity using product price.
- Checkout:
  - Payment cards: Cash, M-PESA (STK simulation), Credit (Deni).
  - Credit reveals customer dropdown.
  - Receipt printing with 58mm layout and `window.print()` via iframe.

### 2.5 Auth and Dashboard

- `resources/views/auth/cinematic.blade.php`:
  - Single cinematic glass card.
  - Alpine toggles between Sign In and Register modes.
  - Forms post to Breeze’s `login` and `register` routes.
  - Google button: `route('auth.google.redirect')` (to be implemented in your Laravel app using Socialite).

- `resources/views/dashboard.blade.php`:
  - Metrics cards:
    - Today’s Sales.
    - Outstanding Deni.
    - Top Product.
    - Payment Mix.
  - ApexCharts placeholders:
    - Sales timeline (area chart).
    - Payment method breakdown (donut).
  - Expects `$metrics` and `$charts` from a controller.

### 2.6 Settings: Backup, Logs, AI chat

- `settings.backup` view:
  - Plain-language guide for shop owners on:
    - Where `database.sqlite` lives.
    - How to back it up to USB (daily/weekly).
    - How to restore a backup.
- `settings.logs` view:
  - Reads `storage/logs/laravel.log`.
  - Shows tail (last ~200 lines) in a scrollable panel for debugging.
- `settings.ai-chat` view:
  - Simple admin UI for connecting to an AI Chat Completion API (example uses `https://api.openai.com/v1/chat/completions`).
  - Input for API key (held in browser state only), question textarea, and a conversation log.
  - Implemented purely in the browser via Alpine + `fetch`.  
    In production, you should move the API calls server-side and store keys securely.

### 2.7 Reports

- Route: `GET /reports/sales` (`reports.sales`).
- Streams a CSV download of sales with optional `from` / `to` date filters.
- Columns: Date, Time, Sale ID, User, Customer, Payment Method, Payment Status, Total Amount.

## 3. Routes overview

Defined in `routes/web.php`:

- Guest:
  - `/login` → `auth.cinematic` (login mode).
  - `/register` → `auth.cinematic` (register mode).
- Auth:
  - `/dashboard` → `dashboard` view.
  - `/pos` → POS terminal with products, speed dial, customers.
  - `/pos/checkout` → JSON/redirect endpoint that creates a sale using `PosService`.
  - `/settings/backup` → backup instructions view.
  - `/settings/logs` → log viewer.
  - `/settings/ai-chat` → AI assistant view.
  - `/reports/sales` → CSV download of sales.

## 4. Console / cloud backup

### 4.1 Cloud backup command

`app/Console/Commands/CloudBackupDatabase.php`:

- `php artisan duka:backup-cloud --disk=s3 --path=backups/database`
- Uploads `database/database.sqlite` to a configured filesystem disk with a timestamped file name.

### 4.2 Console kernel

`app/Console/Kernel.php` (module form):

- Loads commands from `app/Console/Commands`.
- Contains a commented example schedule for nightly cloud backup.

In a real Laravel app, merge this into the existing `App\Console\Kernel` rather than replacing it.

## 5. Dev and launcher scripts

### 5.1 Dev script (for debugging in a full Laravel app)

`dev_run.bat` at repo root is intended to be copied into a *real* Laravel project root (where `artisan` lives). It:

1. Sets `APP_ENV=local`, `APP_DEBUG=true`.
2. Runs `npm run dev` in a separate window (Vite).
3. Runs `php artisan serve --host=127.0.0.1 --port=8000`.

This gives you a fast development loop:

- Run `dev_run.bat`.
- Open `http://127.0.0.1:8000` in your browser.
- Edit Blade, Tailwind classes, Alpine behavior, etc. and see changes live.

### 5.2 Launcher script (desktop bundle)

`launcher.bat` at repo root (intended for a Windows bundle layout):

- Assumes:
  - `php\php.exe` – portable PHP runtime.
  - `duka-app\` – Laravel project root (containing `public/`).
  - Optional: `desktop\Duka-App.exe` – Nativefier-wrapped desktop app.

Behavior:

1. Sets `BASE_DIR`, `DUKA_APP_DIR`, `PHP_EXE`.
2. Changes directory to Laravel app.
3. Sets `APP_ENV=local`, `APP_DEBUG=false`, `APP_URL=http://127.0.0.1:8080`.
4. Starts PHP built-in server:
   - `php.exe -S 127.0.0.1:8080 -t public`.
5. Waits briefly, then:
   - If `desktop/Duka-App.exe` exists, starts it.
   - Else, opens `http://127.0.0.1:8080` in the default browser.

Use case: build an installable folder for dukas and create a shortcut to `launcher.bat` (or a `.vbs` wrapper) as the main POS icon. From the shopkeeper’s perspective, they only double-click an icon.

## 6. One-shot setup scripts (turn this repo into a full Laravel app)

These scripts assume:

- You have **composer**, **PHP**, and **Node/npm** installed.
- You created a folder `duka-app` and placed this repo directly inside it.

### 6.1 Windows: `setup_duka_app.bat`

From a terminal (cmd/PowerShell) in `duka-app`:

```bat
setup_duka_app.bat
```

What it does:

1. Runs `composer create-project laravel/laravel temp-laravel-app`.
2. Copies this repo's `app/`, `database/`, `resources/`, `routes/web.php`, `.env.example`, `dev_run.bat`, `launcher.bat`, and `dev_guide.md` into `temp-laravel-app`.
3. Moves everything from `temp-laravel-app` back into `duka-app`.
4. Deletes `temp-laravel-app`.

Next steps after it finishes:

```bash
# From inside duka-app
cp .env.example .env   # or copy via Explorer
php artisan key:generate
php artisan migrate --seed
npm install
dev_run.bat            # or: php artisan serve & npm run dev
```

### 6.2 Unix/macOS: `setup_duka_app.sh`

From a terminal in `duka-app`:

```bash
chmod +x setup_duka_app.sh
./setup_duka_app.sh
```

It performs the same steps using `rsync`/`cp`.

Afterwards, run:

```bash
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
php artisan serve & npm run dev
```

At this point `duka-app` becomes a full Laravel app with Duka-App features integrated and demo data seeded.

## 7. Is the code complete?

This module provides:

- Migrations and models for all core entities.
- Transactional POS service.
- Midnight Glass layout.
- POS terminal with Kupima, money mode, payment methods, and receipts.
- Cinematic auth view integrated with Breeze routes (frontend).
- Dashboard UI with chart hooks.
- Settings for backup instructions, log viewing, and AI assistant UI.
- Sales report CSV endpoint.
- Cloud backup command.
- Windows dev and launcher scripts.
- Setup scripts (`setup_duka_app.bat`, `setup_duka_app.sh`) to bootstrap a full Laravel app.
- Seeders (`DatabaseSeeder`, `DukaDemoSeeder`) with realistic Kenyan mini-mart data (products, customers, sales, credit scenarios).

What you still need to add *inside the generated Laravel app*:

- Actual Socialite Google auth controller and service configuration.
- Dashboard controller that computes `$metrics` and `$charts`.
- Full PWA/offline sync implementation (service worker, local queue, etc.).
- Secure server-side AI integration (instead of direct browser call with API key).
- Real M-PESA Daraja, bank gateway, and WhatsApp services and callbacks.
- Packaging of PHP + Laravel + Nativefier into an installer or portable bundle.

From a **feature skeleton** perspective, this module plus the setup scripts are complete and ready to be turned into a production-grade Duka-App in a real Laravel environment.

## 7. Is the code complete?

This module provides:

- Migrations and models for all core entities.
- Transactional POS service.
- Midnight Glass layout.
- POS terminal with Kupima, money mode, payment methods, and receipts.
- Cinematic auth view integrated with Breeze routes (frontend).
- Dashboard UI with chart hooks.
- Settings for backup instructions, log viewing, and AI assistant UI.
- Sales report CSV endpoint.
- Cloud backup command.
- Windows launcher script stub.

What you still need to add in a real Laravel project:

- Actual Socialite Google auth controller and service configuration.
- Dashboard controller that computes `$metrics` and `$charts`.
- Full PWA/offline sync implementation (service worker, local queue, etc.).
- Secure server-side AI integration (instead of direct browser call with API key).
- Packaging of PHP + Laravel + Nativefier into an installer or portable bundle.

From a **feature skeleton** perspective, this module is complete and ready to be integrated and finished inside a real Laravel app.