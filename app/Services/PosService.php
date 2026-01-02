<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PosService
{
    /**
     * Calculate the quantity of a loose product from a money amount.
     *
     * Example: KES 50 on sugar at KES 120/kg =&gt; 0.417 kg (rounded to 3dp).
     */
    public function calculateQuantityFromMoney(Product $product, float|int|string $moneyAmount): float
    {
        $amount = (float) $moneyAmount;

        if ($amount &lt;= 0.0 || $product->price &lt;= 0.0) {
            return 0.0;
        }

        $quantity = $amount / (float) $product->price;

        return round($quantity, 3);
    }

    /**
     * Create a sale with items and update inventory/debts in a single transaction.
     *
     * @param  array&lt;int, array{product_id:int, quantity:float|int|string, price:float|int|string}&gt;  $items
     */
    public function createSale(
        User $user,
        ?Customer $customer,
        string $paymentMethod,
        array $items,
        bool $markAsPaid = true,
    ): Sale {
        return DB::transaction(function () use ($user, $customer, $paymentMethod, $items, $markAsPaid): Sale {
            $normalizedItems = $this->normalizeItems($items);

            $totalAmount = $normalizedItems->sum('subtotal');

            $paymentStatus = $this->resolvePaymentStatus($paymentMethod, $markAsPaid);

            $sale = Sale::create([
                'user_id' => $user->id,
                'customer_id' => $customer?->id,
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'total_amount' => $totalAmount,
            ]);

            foreach ($normalizedItems as $item) {
                /** @var \App\Models\Product $product */
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                $saleItem = new SaleItem([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['subtotal'],
                ]);

                $sale->items()->save($saleItem);

                $product->stock_quantity = max(
                    0,
                    (float) $product->stock_quantity - $item['quantity'],
                );
                $product->save();
            }

            if ($paymentMethod === 'credit' &amp;&amp; $customer !== null) {
                $customer->total_debt = (float) $customer->total_debt + $totalAmount;
                $customer->save();
            }

            return $sale->fresh(['items', 'customer', 'user']);
        });
    }

    /**
     * @param  array&lt;int, array{product_id:int, quantity:float|int|string, price:float|int|string}&gt;  $items
     * @return \Illuminate\Support\Collection&lt;int, array{product_id:int, quantity:float, price:float, subtotal:float}&gt;
     */
    protected function normalizeItems(array $items): Collection
    {
        return collect($items)
            -&gt;map(function (array $item): array {
                $quantity = (float) $item['quantity'];
                $price = (float) $item['price'];

                $quantity = round(max($quantity, 0.0), 3);
                $price = round(max($price, 0.0), 2);

                $subtotal = round($quantity * $price, 2);

                return [
                    'product_id' => (int) $item['product_id'],
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ];
            })
            -&gt;filter(fn (array $item): bool =&gt; $item['quantity'] &gt; 0 &amp;&amp; $item['subtotal'] &gt; 0)
            -&gt;values();
    }

    protected function resolvePaymentStatus(string $paymentMethod, bool $markAsPaid): string
    {
        if ($paymentMethod === 'credit') {
            return $markAsPaid ? 'pending' : 'pending';
        }

        return $markAsPaid ? 'paid' : 'pending';
    }
}