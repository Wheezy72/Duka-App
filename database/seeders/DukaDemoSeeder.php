<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DukaDemoSeeder extends Seeder
{
    /**
     * Seed a realistic Kenyan mini-mart dataset.
     */
    public function run(): void
    {
        $this->seedUsers();
        $this->seedProducts();
        $this->seedCustomers();
        $this->seedSales();
    }

    protected function seedUsers(): void
    {
        if (User::query()->exists()) {
            return;
        }

        User::create([
            'name' => 'Admin Wangu',
            'email' => 'admin@duka.local',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Cashier Achieng',
            'email' => 'cashier@duka.local',
            'password' => Hash::make('password'),
            'role' => 'cashier',
        ]);
    }

    protected function seedProducts(): void
    {
        if (Product::query()->exists()) {
            return;
        }

        $baseProducts = [
            ['sku' => 'BREAD400', 'name' => 'Bread 400g', 'price' => 60.00, 'barcode' => '6160000000011', 'is_loose' => false],
            ['sku' => 'BREAD800', 'name' => 'Bread 800g', 'price' => 110.00, 'barcode' => '6160000000012', 'is_loose' => false],
            ['sku' => 'MILK500', 'name' => 'Milk 500ml', 'price' => 60.00, 'barcode' => '6160000000021', 'is_loose' => false],
            ['sku' => 'MILK1L', 'name' => 'Milk 1L', 'price' => 110.00, 'barcode' => '6160000000022', 'is_loose' => false],
            ['sku' => 'SUGAR1KG', 'name' => 'Sugar 1KG (Loose)', 'price' => 220.00, 'barcode' => null, 'is_loose' => true],
            ['sku' => 'UNGA2KG', 'name' => 'Maize Flour 2KG', 'price' => 200.00, 'barcode' => '6160000000031', 'is_loose' => false],
            ['sku' => 'UNGA1KG', 'name' => 'Maize Flour 1KG', 'price' => 110.00, 'barcode' => '6160000000032', 'is_loose' => false],
            ['sku' => 'RICE1KG', 'name' => 'Pishori Rice 1KG (Loose)', 'price' => 260.00, 'barcode' => null, 'is_loose' => true],
            ['sku' => 'TEA250', 'name' => 'Tea Leaves 250g', 'price' => 85.00, 'barcode' => '6160000000041', 'is_loose' => false],
            ['sku' => 'SALT500', 'name' => 'Salt 500g', 'price' => 25.00, 'barcode' => '6160000000051', 'is_loose' => false],
            ['sku' => 'EGG', 'name' => 'Egg (Piece)', 'price' => 18.00, 'barcode' => null, 'is_loose' => false],
            ['sku' => 'SODA500', 'name' => 'Soda 500ml', 'price' => 60.00, 'barcode' => '6160000000061', 'is_loose' => false],
            ['sku' => 'WATER1L', 'name' => 'Drinking Water 1L', 'price' => 50.00, 'barcode' => '6160000000071', 'is_loose' => false],
            ['sku' => 'AIRT100', 'name' => 'Airtime KES 100', 'price' => 100.00, 'barcode' => null, 'is_loose' => false],
            ['sku' => 'AIRT50', 'name' => 'Airtime KES 50', 'price' => 50.00, 'barcode' => null, 'is_loose' => false],
            ['sku' => 'SOAPB', 'name' => 'Bar Soap 800g', 'price' => 150.00, 'barcode' => '6160000000081', 'is_loose' => false],
            ['sku' => 'SOAPL', 'name' => 'Liquid Soap 1L (Loose)', 'price' => 140.00, 'barcode' => null, 'is_loose' => true],
            ['sku' => 'COOKOIL', 'name' => 'Cooking Oil 1L (Loose)', 'price' => 300.00, 'barcode' => null, 'is_loose' => true],
            ['sku' => 'MATCHES', 'name' => 'Matches (Box)', 'price' => 10.00, 'barcode' => '6160000000091', 'is_loose' => false],
            ['sku' => 'CEREAL', 'name' => 'Breakfast Cereal 375g', 'price' => 320.00, 'barcode' => '6160000000101', 'is_loose' => false],
        ];

        $counter = 1;

        // Duplicate pattern with neighbourhood variations until we have 100+ products
        while ($counter <= 5) {
            foreach ($baseProducts as $data) {
                $skuSuffix = $counter > 1 ? '-' . $counter : '';
                $nameSuffix = $counter > 1 ? ' (' . $counter . ')' : '';

                Product::create([
                    'sku' => $data['sku'] . $skuSuffix,
                    'barcode' => $data['barcode'],
                    'name' => $data['name'] . $nameSuffix,
                    'is_loose' => $data['is_loose'],
                    'stock_quantity' => $data['is_loose'] ? 75.000 : 250.000,
                    'price' => $data['price'],
                ]);
            }

            $counter++;
        }
    }

    protected function seedCustomers(): void
    {
        if (Customer::query()->exists()) {
            return;
        }

        $customers = [
            ['name' => 'Mama Njeri', 'phone' => '0722000001'],
            ['name' => 'Baba Otieno', 'phone' => '0722000002'],
            ['name' => 'Mama Achieng', 'phone' => '0722000003'],
            ['name' => 'Karanja Hardware', 'phone' => '0722000004'],
            ['name' => 'Mama Mboga', 'phone' => '0722000005'],
            ['name' => 'Mary Shop', 'phone' => '0722000006'],
            ['name' => 'Juma Rider', 'phone' => '0722000007'],
            ['name' => 'Estate Canteen', 'phone' => '0722000008'],
            ['name' => 'Mama Wairimu', 'phone' => '0722000009'],
            ['name' => 'Otis Wines &amp; Spirits', 'phone' => '0722000010'],
            ['name' => 'Mama Wamboi', 'phone' => '0722000011'],
            ['name' => 'Maina Electronics', 'phone' => '0722000012'],
            ['name' => 'Estate Salon', 'phone' => '0722000013'],
            ['name' => 'Nyama Joint', 'phone' => '0722000014'],
            ['name' => 'Boda Stage Kiosk', 'phone' => '0722000015'],
            ['name' => 'Mama Mumbi', 'phone' => '0722000016'],
            ['name' => 'Estate Butchery', 'phone' => '0722000017'],
            ['name' => 'Mama Aisha', 'phone' => '0722000018'],
            ['name' => 'Flat 3C Canteen', 'phone' => '0722000019'],
            ['name' => 'Estate Bookshop', 'phone' => '0722000020'],
        ];

        foreach ($customers as $data) {
            Customer::create([
                'name' => $data['name'],
                'phone' => $data['phone'],
                'total_debt' => 0,
            ]);
        }
    }

    protected function seedSales(): void
    {
        if (Sale::query()->exists()) {
            return;
        }

        $users = User::all();
        $customers = Customer::all();
        $products = Product::all();

        if ($users->isEmpty() || $products->isEmpty()) {
            return;
        }

        $paymentMethods = ['cash', 'mpesa', 'credit'];

        // Seed 600+ sales over the last 30 days
        for ($i = 0; $i &lt; 600; $i++) {= ['cash', 'mpesa', 'credit'];

        // Seed 120 sales over the last 30 days
        for ($i = 0; $i < 120; $i++) {
            $user = $users->random();
            $method = $paymentMethods[array_rand($paymentMethods)];

            $hasCustomer = $method === 'credit' || (bool) random_int(0, 1);
            $customer = $hasCustomer && $customers->isNotEmpty() ? $customers->random() : null;

            $createdAt = now()
                ->subDays(random_int(0, 29))
                ->setTime(random_int(7, 21), random_int(0, 59));

            $sale = new Sale([
                'user_id' => $user->id,
                'customer_id' => $customer?->id,
                'payment_method' => $method,
                'payment_status' => $method === 'credit' ? 'pending' : 'paid',
                'total_amount' => 0, // updated after items
            ]);

            $sale->created_at = $createdAt;
            $sale->updated_at = $createdAt;
            $sale->save();

            $itemsCount = random_int(1, 6);
            $totalAmount = 0.0;

            $usedProducts = $products->random($itemsCount);

            foreach ($usedProducts as $product) {
                $quantity = $product->is_loose
                    ? random_int(25, 300) / 1000 // 0.025 - 0.300 KG
                    : random_int(1, 4);

                $quantity = round($quantity, 3);
                $price = (float) $product->price;
                $subtotal = round($quantity * $price, 2);

                $saleItem = new SaleItem([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ]);

                $saleItem->created_at = $createdAt;
                $saleItem->updated_at = $createdAt;
                $saleItem->save();

                $totalAmount += $subtotal;

                // Reduce stock (not enforced to stay non-negative in demo)
                $product->stock_quantity = max(0, (float) $product->stock_quantity - $quantity);
                $product->save();
            }

            $sale->total_amount = round($totalAmount, 2);
            $sale->save();

            if ($method === 'credit' && $customer !== null) {
                $customer->total_debt = (float) $customer->total_debt + $sale->total_amount;
                $customer->save();
            }
        }
    }
}