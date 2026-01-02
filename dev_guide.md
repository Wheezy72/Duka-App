# Duka-App Dev Guide

This document explains what this repository contains, how to run it as a Laravel module, and how it fits into a desktop POS bundle with PHP and Nativefier.

## 1. What this is

Duka-App is a **Midnight Glass** themed, offline-first POS feature set for Kenyan dukas, designed to be embedded into a Laravel 11 application.

Key ideas:

- **Complexity in the backend, simplicity in the frontend**.
- Dark-mode POS terminal with glassmorphism and micro-interactions.
- SQLite-friendly and suitable for bundling with a portable PHP runtime and Nativefier into a desktop app.

This repo is **not** a full Laravel framework install (no `artisan`, `composer.json`, etc.). It is a feature module you can drop into a real Laravel project.

## 2. Main features

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

### 2.3 UI Layout (Midnight Glass)

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

## 5. Launcher script (desktop bundle)

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

Use case: build an installable folder for dukas and create a shortcut to `launcher.bat` (or a `.vbs` wrapper) as the main POS icon.

## 6. How to integrate into a full Laravel 11 app

1. **Create Laravel app**

   ```bash
   laravel new duka-app
   cd duka-app
   ```

2. **Install Breeze and Socialite**

   ```bash
   composer require laravel/breeze --dev
   php artisan breeze:install blade

   composer require laravel/socialite
   php artisan migrate
   npm install && npm run dev
   ```

3. **Copy this module**

   From this repo into your Laravel app:

   - `app/Models/{Product,Customer,Sale,SaleItem}.php`
   - `app/Services/PosService.php`
   - `database/migrations/*_create_{users,products,customers,sales,sale_items}_table.php` (merge with existing user migration as needed).
   - `resources/views/layouts/app.blade.php` (or adapt into your layout).
   - `resources/views/{auth,dashboard,pos,settings}`.
   - `routes/web.php` POS + Settings + Reports routes (merge into your existing `web.php`).
   - `app/Console/Commands/CloudBackupDatabase.php` and merge console kernel.

4. **Configure auth & Socialite**

   - Implement `Auth\GoogleController` (or equivalent) and routes:

     ```php
     Route::get('/auth/google/redirect', [GoogleController::class, 'redirect'])->name('auth.google.redirect');
     Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('auth.google.callback');
     ```

   - In `config/services.php`, set up Google.
   - In `.env`, add `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI`.

5. **SQLite / DB configuration (for desktop)**

   - Set `DB_CONNECTION=sqlite` and `DB_DATABASE=database/database.sqlite` in `.env`.
   - Create file:

     ```bash
     mkdir -p database
     touch database/database.sqlite
     php artisan migrate --force
     ```

6. **Assets**

   - Place logos and icons in `public/assets/`.
   - Ensure `resources/css/app.css` and `resources/js/app.js` are wired with Tailwind, Alpine, and ApexCharts.

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