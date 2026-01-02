<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Services\PosService;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Support\Facades\File;

Route::redirect('/', '/dashboard');

Route::middleware(['guest'])->group(function () {
    Route::view('/login', 'auth.cinematic')->name('login');
    Route::view('/register', 'auth.cinematic')->name('register');
});

Route::middleware(['auth'])->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');

    Route::get('/pos', function () {
        $products = Product::orderBy('name')->get();

        $speedDialProducts = Product::whereIn('sku', [
            'BREAD',
            'MILK',
            'AIRTIME',
        ])->get();

        $customers = \App\Models\Customer::orderBy('name')->get();

        return view('pos.terminal', [
            'products' => $products,
            'speedDialProducts' => $speedDialProducts,
            'customers' => $customers,
        ]);
    })->name('pos.terminal');

    Route::post('/pos/checkout', function (Request $request, PosService $posService) {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:cash,mpesa,credit'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
        ]);

        $customer = null;

        if (!empty($data['customer_id'])) {
            $customer = Customer::find($data['customer_id']);
        }

        $sale = $posService->createSale(
            user: $request->user(),
            customer: $customer,
            paymentMethod: $data['payment_method'],
            items: $data['items'],
            markAsPaid: $data['payment_method'] !== 'credit',
        );

        if ($request->wantsJson()) {
            return response()->json([
                'sale' => $sale->load('items', 'customer', 'user'),
            ]);
        }

        return redirect()
            ->route('pos.terminal')
            ->with('status', 'Sale recorded successfully.');
    })->name('pos.checkout');

    Route::view('/settings/backup', 'settings.backup')->name('settings.backup');

    Route::get('/settings/logs', function () {
        $logFile = storage_path('logs/laravel.log');

        $logs = '';

        if (File::exists($logFile)) {
            $contents = File::get($logFile);
            $lines = explode(PHP_EOL, trim($contents));
            $logs = implode(PHP_EOL, array_slice($lines, max(count($lines) - 200, 0)));
        }

        return view('settings.logs', [
            'logs' => $logs,
        ]);
    })->name('settings.logs');
});

// Auth routes (to be wired with Breeze/Socialite in a full Laravel app)