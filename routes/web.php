<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Services\PosService;
use App\Models\Customer;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');

    Route::view('/pos', 'pos.terminal')->name('pos.terminal');

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
});

require __DIR__ . '/auth.php';