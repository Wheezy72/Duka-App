<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Services\PosService;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
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
            'BREAD400',
            'BREAD800',
            'AIRT100',
            'AIRT50',
        ])->take(12)->get();

        $customers = Customer::orderBy('name')->get();

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

    Route::get('/reports/sales', function (Request $request) {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $query = Sale::with(['user', 'customer'])->orderByDesc('created_at');

        if (! empty($validated['from'])) {
            $query->whereDate('created_at', '>=', $validated['from']);
        }

        if (! empty($validated['to'])) {
            $query->whereDate('created_at', '<=', $validated['to']);
        }

        $sales = $query->get();

        $headers = [
            'Date',
            'Time',
            'Sale ID',
            'User',
            'Customer',
            'Payment Method',
            'Payment Status',
            'Total Amount',
        ];

        $rows = $sales->map(function (Sale $sale): array {
            return [
                $sale->created_at->format('Y-m-d'),
                $sale->created_at->format('H:i:s'),
                (string) $sale->id,
                $sale->user?->name ?? '',
                $sale->customer?->name ?? '',
                $sale->payment_method,
                $sale->payment_status,
                number_format((float) $sale->total_amount, 2, '.', ''),
            ];
        });

        $callback = static function () use ($headers, $rows): void {
            $handle = fopen('php://output', 'wb');

            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        };

        $fileName = 'duka-sales-' . now()->format('Y-m-d_H-i-s') . '.csv';

        return response()->streamDownload($callback, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    })->name('reports.sales');

    Route::view('/settings/ai-chat', 'settings.ai-chat')->name('settings.ai-chat');
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

    Route::get('/reports/sales', function (Request $request) {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $query = Sale::with(['user', 'customer'])->orderByDesc('created_at');

        if (! empty($validated['from'])) {
            $query->whereDate('created_at', '>=', $validated['from']);
        }

        if (! empty($validated['to'])) {
            $query->whereDate('created_at', '<=', $validated['to']);
        }

        $sales = $query->get();

        $headers = [
            'Date',
            'Time',
            'Sale ID',
            'User',
            'Customer',
            'Payment Method',
            'Payment Status',
            'Total Amount',
        ];

        $rows = $sales->map(function (Sale $sale): array {
            return [
                $sale->created_at->format('Y-m-d'),
                $sale->created_at->format('H:i:s'),
                (string) $sale->id,
                $sale->user?->name ?? '',
                $sale->customer?->name ?? '',
                $sale->payment_method,
                $sale->payment_status,
                number_format((float) $sale->total_amount, 2, '.', ''),
            ];
        });

        $callback = static function () use ($headers, $rows): void {
            $handle = fopen('php://output', 'wb');

            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        };

        $fileName = 'duka-sales-' . now()->format('Y-m-d_H-i-s') . '.csv';

        return response()->streamDownload($callback, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    })->name('reports.sales');

    Route::view('/settings/ai-chat', 'settings.ai-chat')->name('settings.ai-chat');
});

// Auth routes (to be wired with Breeze/Socialite in a full Laravel app)