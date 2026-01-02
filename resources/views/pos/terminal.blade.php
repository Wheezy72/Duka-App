@extends('layouts.app')

@section('page-header')
    <div class="flex items-center justify-between">
        <div>
            <p class="text-[11px] uppercase tracking-[0.18em] text-slate-400 mb-1">Point of Sale</p>
            <h1 class="text-xl md:text-2xl font-semibold tracking-tight text-slate-50">Speed POS Terminal</h1>
            <p class="text-xs text-slate-400 mt-1">Scan, tap, and checkout in seconds.</p>
        </div>
        <div class="hidden md:flex items-center space-x-2 text-[11px] text-slate-400">
            <span>Dashboard</span>
            <span class="text-slate-500">/</span>
            <span class="text-cyan-300">POS Terminal</span>
        </div>
    </div>
@endsection

@section('content')
    @php
        $products = $products ?? [];
        $speedDialProducts = $speedDialProducts ?? [];
        $customers = $customers ?? [];
    @endphp

    <div
        x-data="posTerminal({
            products: @json($products),
            speedDialProducts: @json($speedDialProducts),
            customers: @json($customers),
            checkoutUrl: '{{ route('pos.checkout') }}',
            csrfToken: '{{ csrf_token() }}',
        })"
        x-init="init()"
        class="grid gap-4 md:gap-6 md:grid-cols-5"
    >
        <!-- Left: Product search &amp; speed dial -->
        <section class="md:col-span-3 space-y-4">
            <!-- Search / barcode input -->
            <div class="glass-panel px-4 py-3 md:px-5 md:py-4">
                <div class="flex flex-col md:flex-row md:items-center md:space-x-3 space-y-2 md:space-y-0">
                    <div class="flex-1">
                        <label class="flex items-center justify-between text-[11px] text-slate-400 mb-1">
                            <span>Scan barcode or search product</span>
                            <span class="hidden md:inline-flex items-center space-x-1">
                                <span class="rounded-md border border-white/15 bg-white/5 px-1.5 py-0.5 text-[10px]">Enter</span>
                                <span>to add</span>
                            </span>
                        </label>
                        <div class="relative">
                            <input
                                type="text"
                                x-model="searchTerm"
                                @keydown.enter.prevent="handleSearchSubmit"
                                @input.debounce.250ms="handleSearchInput"
                                placeholder="Scan barcode, type product name, or SKU..."
                                class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs text-slate-100 placeholder:text-slate-500 focus:border-cyan-400 focus:outline-none focus:ring-0"
                            >
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                <span class="rounded-md border border-white/15 bg-white/5 px-1.5 py-0.5 text-[10px] text-slate-400">Ctrl + K</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2 text-[11px] text-slate-400 md:w-48">
                        <div class="flex-1">
                            <p class="text-slate-300">Items</p>
                            <p class="text-xs font-medium text-cyan-300" x-text="cart.length"></p>
                        </div>
                        <div class="flex-1 text-right">
                            <p class="text-slate-300">Running Total</p>
                            <p class="text-xs font-semibold text-emerald-300" x-text="formatCurrency(cartTotal)"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Speed dial items -->
            <div class="glass-panel px-4 py-3 md:px-5 md:py-4">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-[11px] font-medium uppercase tracking-[0.18em] text-slate-400">Speed Dial</p>
                    <p class="text-[11px] text-slate-500">Tap to add high-volume items</p>
                </div>
                <div class="grid grid-cols-3 md:grid-cols-6 gap-2">
                    <template x-for="product in speedDialProducts" :key="product.id">
                        <button
                            type="button"
                            @click="addProduct(product)"
                            class="group flex flex-col items-start justify-between rounded-xl bg-white/5 px-3 py-2 text-left text-[11px] text-slate-100 shadow-lg shadow-black/30 transition-transform duration-150 hover:bg-white/10 active:scale-95"
                        >
                            <span class="font-medium line-clamp-1" x-text="product.name"></span>
                            <span class="mt-1 text-[10px] text-cyan-300" x-text="formatCurrency(product.price)"></span>
                        </button>
                    </template>
                    <template x-if="speedDialProducts.length === 0">
                        <p class="col-span-3 md:col-span-6 text-[11px] text-slate-500">
                            Configure speed dial products from your Inventory.
                        </p>
                    </template>
                </div>
            </div>

            <!-- Product grid -->
            <div class="glass-panel px-4 py-3 md:px-5 md:py-4">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-[11px] font-medium uppercase tracking-[0.18em] text-slate-400">Products</p>
                    <p class="text-[11px] text-slate-500">
                        <span x-text="filteredProducts.length"></span>
                        <span> items</span>
                    </p>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 max-h-[420px] overflow-y-auto pr-1">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <button
                            type="button"
                            @click="handleProductClick(product)"
                            class="group flex flex-col items-start justify-between rounded-xl bg-white/5 px-3 py-2 text-left text-[11px] text-slate-100 shadow-lg shadow-black/30 transition-transform duration-150 hover:bg-white/10 active:scale-95"
                        >
                            <div class="flex items-center justify-between w-full">
                                <span class="font-medium line-clamp-2" x-text="product.name"></span>
                                <span
                                    class="ml-2 rounded-full px-1.5 py-0.5 text-[10px]"
                                    :class="product.is_loose ? 'bg-amber-500/20 text-amber-300' : 'bg-emerald-500/20 text-emerald-300'"
                                    x-text="product.is_loose ? 'Loose' : 'Unit'"
                                ></span>
                            </div>
                            <div class="mt-1 flex items-center justify-between w-full text-[10px] text-slate-400">
                                <span x-text="'Stock: ' + formatQuantity(product.stock_quantity)"></span>
                                <span class="text-cyan-300 font-medium" x-text="formatCurrency(product.price)"></span>
                            </div>
                        </button>
                    </template>

                    <template x-if="filteredProducts.length === 0">
                        <p class="col-span-2 md:col-span-4 text-[11px] text-slate-500">
                            No products match your search.
                        </p>
                    </template>
                </div>
            </div>
        </section>

        <!-- Right: Cart &amp; checkout -->
        <section class="md:col-span-2 space-y-4">
            <!-- Cart -->
            <div class="glass-panel px-4 py-3 md:px-5 md:py-4">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-[11px] font-medium uppercase tracking-[0.18em] text-slate-400">Cart</p>
                        <p class="text-xs text-slate-500" x-show="cart.length === 0">No items yet. Add from the left.</p>
                    </div>
                    <button
                        type="button"
                        @click="clearCart"
                        x-show="cart.length &gt; 0"
                        class="inline-flex items-center rounded-xl border border-white/15 bg-white/5 px-2.5 py-1 text-[11px] text-slate-300 shadow-lg shadow-black/40 transition-transform duration-150 hover:bg-white/10 active:scale-95"
                    >
                        Clear
                    </button>
                </div>

                <div class="max-h-64 overflow-y-auto pr-1">
                    <template x-for="item in cart" :key="item.id">
                        <div
                            class="flex items-center justify-between rounded-xl px-3 py-2 text-[11px] text-slate-100 hover:bg-white/5 transition-colors"
                        >
                            <div class="flex-1">
                                <p class="font-medium line-clamp-1" x-text="item.name"></p>
                                <p class="text-[10px] text-slate-400">
                                    <span x-text="formatQuantity(item.quantity)"></span>
                                    <span> × </span>
                                    <span x-text="formatCurrency(item.price)"></span>
                                </p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="flex items-center rounded-full bg-white/5">
                                    <button
                                        type="button"
                                        @click="decrementItem(item)"
                                        class="inline-flex h-6 w-6 items-center justify-center text-[12px] text-slate-300 hover:text-rose-300"
                                    >
                                        –
                                    </button>
                                    <span class="w-10 text-center text-[11px]" x-text="formatQuantity(item.quantity)"></span>
                                    <button
                                        type="button"
                                        @click="incrementItem(item)"
                                        class="inline-flex h-6 w-6 items-center justify-center text-[12px] text-slate-300 hover:text-emerald-300"
                                    >
                                        +
                                    </button>
                                </div>
                                <p class="w-16 text-right text-[11px] font-semibold text-emerald-300" x-text="formatCurrency(item.subtotal)"></p>
                                <button
                                    type="button"
                                    @click="removeItem(item)"
                                    class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-rose-500/10 text-[12px] text-rose-300 hover:bg-rose-500/20 transition-transform duration-150 active:scale-95"
                                >
                                    ×
                                </button>
                            </div>
                        </div>
                    </template>

                    <template x-if="cart.length === 0">
                        <p class="text-[11px] text-slate-500">Cart is empty.</p>
                    </template>
                </div>

                <div class="mt-3 flex items-center justify-between border-t border-white/10 pt-3">
                    <div class="text-[11px] text-slate-400">
                        <p>Items: <span x-text="cart.length"></span></p>
                    </div>
                    <div class="text-right">
                        <p class="text-[11px] text-slate-400">Total Due</p>
                        <p class="text-lg font-semibold tracking-tight text-emerald-300" x-text="formatCurrency(cartTotal)"></p>
                    </div>
                </div>
            </div>

            <!-- Payment &amp; checkout -->
            <div class="glass-panel px-4 py-3 md:px-5 md:py-4 space-y-4">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-medium uppercase tracking-[0.18em] text-slate-400">Payment</p>
                    <p class="text-[11px] text-slate-500">Select method &amp; complete sale</p>
                </div>

                <!-- Payment methods -->
                <div class="grid grid-cols-3 gap-2">
                    <button
                        type="button"
                        @click="selectPaymentMethod('cash')"
                        class="payment-card"
                        :class="paymentMethod === 'cash' ? 'payment-card-active border-emerald-500/60 text-emerald-300' : ''"
                    >
                        <span class="text-[11px] font-medium">Cash</span>
                        <span class="mt-1 text-[10px] text-slate-400">KES</span>
                    </button>
                    <button
                        type="button"
                        @click="handleMpesaClick"
                        class="payment-card"
                        :class="paymentMethod === 'mpesa' ? 'payment-card-active border-cyan-500/60 text-cyan-300' : ''"
                    >
                        <span class="text-[11px] font-medium">M-PESA</span>
                        <span class="mt-1 text-[10px] text-slate-400">STK Push</span>
                    </button>
                    <button
                        type="button"
                        @click="selectPaymentMethod('credit')"
                        class="payment-card"
                        :class="paymentMethod === 'credit' ? 'payment-card-active border-rose-500/60 text-rose-300' : ''"
                    >
                        <span class="text-[11px] font-medium">DENI / CREDIT</span>
                        <span class="mt-1 text-[10px] text-slate-400">Customer account</span>
                    </button>
                </div>

                <!-- Credit customer selection -->
                <div x-show="paymentMethod === 'credit'" x-transition class="space-y-2">
                    <label class="text-[11px] text-slate-400">Select customer</label>
                    <select
                        x-model="selectedCustomerId"
                        class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs text-slate-100 focus:border-rose-400 focus:outline-none focus:ring-0"
                    >
                        <option value="">- Choose customer -</option>
                        <template x-for="customer in customers" :key="customer.id">
                            <option :value="customer.id" x-text="customer.name + ' • ' + (customer.phone ?? '')"></option>
                        </template>
                    </select>
                </div>

                <!-- M-PESA simulation -->
                <div x-show="isProcessingMpesa" x-transition class="flex items-center space-x-2 text-[11px] text-cyan-300">
                    <svg class="h-4 w-4 animate-spin text-cyan-400" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3.5-3.5L12 0v4a8 8 0 00-8 8h4z"></path>
                    </svg>
                    <span>Simulating STK Push...</span>
                </div>

                <!-- Checkout button -->
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] text-slate-400">Total Due</p>
                        <p class="text-2xl font-semibold tracking-tight text-emerald-300" x-text="'KES ' + formatCurrency(cartTotal, false)"></p>
                    </div>
                    <button
                        type="button"
                        @click="submitCheckout"
                        :disabled="cart.length === 0 || !paymentMethod || (paymentMethod === 'credit' &amp;&amp; !selectedCustomerId) || isSubmitting"
                        class="inline-flex items-center justify-center rounded-2xl bg-cyan-500 px-5 py-2.5 text-xs font-semibold tracking-tight text-slate-950 shadow-lg shadow-cyan-500/40 transition-transform duration-150 hover:bg-cyan-400 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <span x-show="!isSubmitting">Complete Sale</span>
                        <span x-show="isSubmitting" class="flex items-center space-x-2">
                            <svg class="h-4 w-4 animate-spin text-slate-900" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3.5-3.5L12 0v4a8 8 0 00-8 8h4z"></path>
                            </svg>
                            <span>Processing...</span>
                        </span>
                    </button>
                </div>
            </div>
        </section>

        <!-- Kupima modal -->
        <div
            x-show="showKupima"
            x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center px-4"
        >
            <div class="absolute inset-0 bg-slate-950/60"></div>
            <div
                @click.away="closeKupima"
                class="relative glass-panel w-full max-w-sm px-5 py-4"
            >
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <p class="text-[11px] uppercase tracking-[0.18em] text-slate-400 mb-1">Kupima</p>
                        <h2 class="text-sm font-semibold tracking-tight text-slate-50" x-text="kupimaProduct?.name ?? 'Loose item'"></h2>
                    </div>
                    <button
                        type="button"
                        @click="closeKupima"
                        class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-white/5 text-[12px] text-slate-300 hover:bg-white/10 transition-transform duration-150 active:scale-95"
                    >
                        ×
                    </button>
                </div>

                <div class="space-y-3">
                    <div>
                        <p class="text-[11px] text-slate-400 mb-1">Quick weights</p>
                        <div class="flex items-center space-x-2">
                            <template x-for="option in kupimaWeightOptions" :key="option">
                                <button
                                    type="button"
                                    @click="applyWeight(option)"
                                    class="inline-flex flex-1 items-center justify-center rounded-xl bg-white/5 px-2.5 py-1.5 text-[11px] text-slate-100 shadow-lg shadow-black/40 transition-transform duration-150 hover:bg-white/10 active:scale-95"
                                >
                                    <span x-text="formatWeightOption(option)"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <div class="border-t border-white/10 pt-3">
                        <p class="text-[11px] text-slate-400 mb-1">Money mode (KES)</p>
                        <div class="flex items-center space-x-2">
                            <input
                                type="number"
                                min="0"
                                step="10"
                                x-model="kupimaMoney"
                                placeholder="Enter amount e.g. 50"
                                class="flex-1 rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs text-slate-100 placeholder:text-slate-500 focus:border-cyan-400 focus:outline-none focus:ring-0"
                            >
                            <button
                                type="button"
                                @click="applyMoney"
                                class="inline-flex items-center justify-center rounded-xl bg-cyan-500 px-3 py-2 text-[11px] font-semibold text-slate-950 shadow-lg shadow-cyan-500/40 transition-transform duration-150 hover:bg-cyan-400 active:scale-95"
                            >
                                Apply
                            </button>
                        </div>
                        <p class="mt-1 text-[11px] text-slate-500">
                            KES <span x-text="kupimaMoney || 0"></span>
                            @ price
                            <span x-text="formatCurrency(kupimaProduct?.price || 0)"></span>
                            =&gt;
                            <span class="font-medium text-emerald-300" x-text="previewKupimaQuantity"></span> kg
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Receipt print area -->
        <div id="receipt-print-area" class="hidden">
            <div class="w-[58mm] bg-white text-slate-900 text-[11px] p-3 space-y-2">
                <div class="text-center">
                    <p class="text-xs font-semibold tracking-tight">Duka-App Receipt</p>
                    <p class="text-[10px] text-slate-500">{{ config('app.name', 'Duka-App') }}</p>
                </div>
                <div class="border-t border-slate-200 pt-1">
                    <template x-for="item in cart" :key="item.id">
                        <div class="flex justify-between">
                            <div>
                                <p x-text="item.name"></p>
                                <p class="text-[10px] text-slate-500">
                                    <span x-text="formatQuantity(item.quantity)"></span>
                                    ×
                                    <span x-text="formatCurrency(item.price)"></span>
                                </p>
                            </div>
                            <p class="text-right" x-text="formatCurrency(item.subtotal)"></p>
                        </div>
                    </template>
                </div>
                <div class="border-t border-slate-200 pt-1 flex justify-between">
                    <span>Total</span>
                    <span x-text="'KES ' + formatCurrency(cartTotal, false)"></span>
                </div>
                <p class="text-center text-[10px] text-slate-500 pt-1">Asante kwa kununua!</p>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () =&gt; {
                Alpine.data('posTerminal', (config) =&gt; ({
                    products: config.products || [],
                    speedDialProducts: config.speedDialProducts || [],
                    customers: config.customers || [],
                    checkoutUrl: config.checkoutUrl,
                    csrfToken: config.csrfToken,

                    searchTerm: '',
                    filteredProducts: [],
                    cart: [],
                    paymentMethod: null,
                    selectedCustomerId: '',
                    isProcessingMpesa: false,
                    isSubmitting: false,

                    showKupima: false,
                    kupimaProduct: null,
                    kupimaWeightOptions: [0.25, 0.5, 1],
                    kupimaMoney: '',
                    previewKupimaQuantity: '0.000',

                    get cartTotal() {
                        return this.cart.reduce((total, item) =&gt; {
                            return total + (item.subtotal || 0);
                        }, 0);
                    },

                    init() {
                        this.filteredProducts = this.products;
                    },

                    handleSearchInput() {
                        const term = this.searchTerm.toLowerCase().trim();

                        if (!term) {
                            this.filteredProducts = this.products;
                            return;
                        }

                        this.filteredProducts = this.products.filter((product) =&gt; {
                            return (
                                String(product.name).toLowerCase().includes(term) ||
                                String(product.sku || '').toLowerCase().includes(term) ||
                                String(product.barcode || '').toLowerCase().includes(term)
                            );
                        });
                    },

                    handleSearchSubmit() {
                        if (this.filteredProducts.length === 1) {
                            this.handleProductClick(this.filteredProducts[0]);
                            this.searchTerm = '';
                            this.filteredProducts = this.products;
                        }
                    },

                    handleProductClick(product) {
                        if (product.is_loose) {
                            this.openKupima(product);
                        } else {
                            this.addProduct(product);
                        }
                    },

                    addProduct(product, quantity = 1) {
                        const existing = this.cart.find((item) =&gt; item.id === product.id);

                        const price = Number(product.price || 0);
                        const qty = Number(quantity);

                        if (existing) {
                            existing.quantity = Number((existing.quantity + qty).toFixed(3));
                            existing.subtotal = Number((existing.quantity * existing.price).toFixed(2));
                        } else {
                            this.cart.push({
                                id: product.id,
                                name: product.name,
                                price: price,
                                quantity: qty,
                                subtotal: Number((qty * price).toFixed(2)),
                            });
                        }
                    },

                    incrementItem(item) {
                        item.quantity = Number((item.quantity + 1).toFixed(3));
                        item.subtotal = Number((item.quantity * item.price).toFixed(2));
                    },

                    decrementItem(item) {
                        if (item.quantity &lt;= 1) {
                            this.removeItem(item);
                            return;
                        }

                        item.quantity = Number((item.quantity - 1).toFixed(3));
                        item.subtotal = Number((item.quantity * item.price).toFixed(2));
                    },

                    removeItem(item) {
                        this.cart = this.cart.filter((i) =&gt; i.id !== item.id);
                    },

                    clearCart() {
                        this.cart = [];
                        this.paymentMethod = null;
                        this.selectedCustomerId = '';
                    },

                    selectPaymentMethod(method) {
                        this.paymentMethod = method;
                    },

                    handleMpesaClick() {
                        this.paymentMethod = 'mpesa';
                        this.isProcessingMpesa = true;

                        setTimeout(() =&gt; {
                            this.isProcessingMpesa = false;
                        }, 2000);
                    },

                    // Kupima
                    openKupima(product) {
                        this.kupimaProduct = product;
                        this.kupimaMoney = '';
                        this.previewKupimaQuantity = '0.000';
                        this.showKupima = true;
                    },

                    closeKupima() {
                        this.showKupima = false;
                        this.kupimaProduct = null;
                        this.kupimaMoney = '';
                        this.previewKupimaQuantity = '0.000';
                    },

                    formatWeightOption(option) {
                        if (option === 0.25) return '1/4 KG';
                        if (option === 0.5) return '1/2 KG';
                        if (option === 1) return '1 KG';
                        return option + ' KG';
                    },

                    applyWeight(weight) {
                        if (!this.kupimaProduct) return;

                        this.addProduct(this.kupimaProduct, weight);
                        this.closeKupima();
                    },

                    applyMoney() {
                        if (!this.kupimaProduct) return;

                        const money = Number(this.kupimaMoney || 0);
                        const price = Number(this.kupimaProduct.price || 0);

                        if (money &lt;= 0 || price &lt;= 0) {
                            return;
                        }

                        const quantity = Number((money / price).toFixed(3));
                        this.addProduct(this.kupimaProduct, quantity);
                        this.closeKupima();
                    },

                    // Formatting helpers
                    formatCurrency(value, withDecimals = true) {
                        const num = Number(value || 0);
                        return num.toLocaleString('en-KE', {
                            minimumFractionDigits: withDecimals ? 2 : 0,
                            maximumFractionDigits: withDecimals ? 2 : 0,
                        });
                    },

                    formatQuantity(value) {
                        const num = Number(value || 0);
                        return num.toFixed(3);
                    },

                    // Checkout
                    async submitCheckout() {
                        if (this.cart.length === 0 || !this.paymentMethod) {
                            return;
                        }

                        if (this.paymentMethod === 'credit' &amp;&amp; !this.selectedCustomerId) {
                            return;
                        }

                        this.isSubmitting = true;

                        try {
                            const response = await fetch(this.checkoutUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': this.csrfToken,
                                },
                                body: JSON.stringify({
                                    items: this.cart.map((item) =&gt; ({
                                        product_id: item.id,
                                        quantity: item.quantity,
                                        price: item.price,
                                    })),
                                    payment_method: this.paymentMethod,
                                    customer_id: this.paymentMethod === 'credit' ? this.selectedCustomerId : null,
                                }),
                            });

                            if (!response.ok) {
                                throw new Error('Checkout failed');
                            }

                            const data = await response.json();

                            this.printReceipt();
                            this.clearCart();

                            document.dispatchEvent(new CustomEvent('toast', {
                                detail: {
                                    message: 'Sale completed successfully.',
                                },
                            }));

                        } catch (error) {
                            console.error(error);
                        } finally {
                            this.isSubmitting = false;
                        }
                    },

                    printReceipt() {
                        const iframe = document.createElement('iframe');
                        iframe.style.position = 'fixed';
                        iframe.style.right = '0';
                        iframe.style.bottom = '0';
                        iframe.style.width = '0';
                        iframe.style.height = '0';
                        iframe.style.border = '0';

                        document.body.appendChild(iframe);

                        const doc = iframe.contentWindow.document;
                        const receipt = document.querySelector('#receipt-print-area').innerHTML;

                        doc.open();
                        doc.write('&lt;html&gt;&lt;head&gt;&lt;title&gt;Receipt&lt;/title&gt;&lt;/head&gt;&lt;body&gt;' + receipt + '&lt;/body&gt;&lt;/html&gt;');
                        doc.close();

                        iframe.contentWindow.focus();
                        iframe.contentWindow.print();

                        setTimeout(() =&gt; {
                            document.body.removeChild(iframe);
                        }, 1000);
                    },
                }));
            });
        </script>
    @endpush
@endsection