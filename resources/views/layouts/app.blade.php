<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ mobileNavOpen: false }" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Duka-App') }}</title>

        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link
            href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;family=Outfit:wght@400;500;600;700&amp;display=swap"
            rel="stylesheet"
        >

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body {
                font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            }

            @media print {
                body * {
                    visibility: hidden;
                }

                #receipt-print-area, #receipt-print-area * {
                    visibility: visible;
                }

                #receipt-print-area {
                    position: absolute;
                    inset: 0;
                    margin: 0 auto;
                }
            }
        </style>
    </head>
    <body class="h-full bg-gradient-to-br from-[#0f172a] via-[#1e1b4b] to-[#0f172a] text-slate-100 antialiased">
        <div class="min-h-screen flex">
            <!-- Sidebar -->
            <aside class="hidden md:flex md:flex-col w-64 m-4 glass-panel">
                <div class="flex items-center justify-between px-5 pt-5 pb-4 border-b border-white/10">
                    <div class="flex items-center space-x-3">
                        <div class="h-9 w-9 rounded-2xl bg-cyan-500/90 flex items-center justify-center shadow-lg shadow-cyan-500/40">
                            <span class="text-xs font-bold tracking-widest text-slate-950">DA</span>
                        </div>
                        <div>
                            <p class="text-sm font-semibold tracking-tight">Duka-App</p>
                            <p class="text-[11px] text-slate-400">Midnight Glass POS</p>
                        </div>
                    </div>
                </div>

                <nav class="flex-1 px-3 pt-4 pb-3 space-y-1 overflow-y-auto">
                    <a
                        href="{{ route('dashboard') }}"
                        class="nav-link {{ request()->routeIs('dashboard') ? 'nav-link-active' : '' }}"
                    >
                        <span class="text-xs font-medium">Dashboard</span>
                    </a>
                    <a
                        href="{{ route('pos.terminal') }}"
                        class="nav-link {{ request()->routeIs('pos.*') ? 'nav-link-active' : '' }}"
                    >
                        <span class="text-xs font-medium">POS Terminal</span>
                    </a>
                    <a href="#" class="nav-link">
                        <span class="text-xs font-medium">Inventory</span>
                    </a>
                    <a href="#" class="nav-link">
                        <span class="text-xs font-medium">Debts</span>
                    </a>
                    <a href="#" class="nav-link">
                        <span class="text-xs font-medium">Settings</span>
                    </a>
                </nav>

                <div class="mt-auto border-t border-white/10 px-4 py-4">
                    <div class="flex items-center space-x-3">
                        <div class="h-9 w-9 rounded-full bg-white/10 flex items-center justify-center">
                            <span class="text-[11px] font-semibold">{{ strtoupper(\Illuminate\Support\Str::substr(auth()->user()->name ?? 'DU', 0, 2)) }}</span>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs font-semibold leading-tight">{{ auth()->user()->name ?? 'Duka User' }}</p>
                            <p class="text-[11px] text-slate-400 leading-tight">{{ auth()->user()->email ?? 'cashier@duka.local' }}</p>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Mobile top bar -->
            <header class="fixed inset-x-0 top-0 z-40 flex items-center justify-between px-4 py-3 md:hidden">
                <div class="flex items-center space-x-3">
                    <div class="h-9 w-9 rounded-2xl bg-cyan-500/90 flex items-center justify-center shadow-lg shadow-cyan-500/40">
                        <span class="text-xs font-bold tracking-widest text-slate-950">DA</span>
                    </div>
                    <div>
                        <p class="text-sm font-semibold tracking-tight">Duka-App</p>
                        <p class="text-[11px] text-slate-400">Midnight Glass POS</p>
                    </div>
                </div>
                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-xl border border-white/15 bg-white/10 px-3 py-1.5 text-xs font-medium text-slate-100 shadow-lg shadow-black/40 transition-transform duration-150 hover:bg-white/15 active:scale-95"
                    @click="mobileNavOpen = !mobileNavOpen"
                >
                    <span x-show="!mobileNavOpen">Menu</span>
                    <span x-show="mobileNavOpen">Close</span>
                </button>
            </header>

            <!-- Mobile bottom nav -->
            <nav
                class="fixed inset-x-0 bottom-0 z-40 px-4 pb-4 md:hidden"
                x-show="mobileNavOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-3"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-3"
            >
                <div class="glass-panel flex items-center justify-around px-3 py-2">
                    <a href="{{ route('dashboard') }}" class="mobile-nav-link {{ request()->routeIs('dashboard') ? 'mobile-nav-link-active' : '' }}">Dash</a>
                    <a href="{{ route('pos.terminal') }}" class="mobile-nav-link {{ request()->routeIs('pos.*') ? 'mobile-nav-link-active' : '' }}">POS</a>
                    <a href="#" class="mobile-nav-link">Stock</a>
                    <a href="#" class="mobile-nav-link">Debts</a>
                </div>
            </nav>

            <!-- Main -->
            <div class="flex-1 flex flex-col md:ml-72">
                <main class="flex-1 px-4 pt-20 pb-24 md:px-8 md:pt-6 md:pb-8">
                    <!-- Page header -->
                    @hasSection('page-header')
                        <div class="mb-6">
                            @yield('page-header')
                        </div>
                    @endif

                    <!-- Content -->
                    <div class="space-y-6">
                        @yield('content')
                    </div>
                </main>
            </div>
        </div>

        <!-- Toast notifications -->
        @if (session('status'))
            <div
                x-data="{ show: true }"
                x-init="setTimeout(() =&gt; show = false, 3500)"
                x-show="show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-3"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-2"
                class="fixed top-4 right-4 z-50 max-w-sm glass-panel px-4 py-3 text-xs"
            >
                <p class="font-medium text-emerald-300">{{ session('status') }}</p>
            </div>
        @endif

        @stack('modals')
        @stack('scripts')
    </body>
</html>

@php
    // Tailwind utility compositions for re-use in templates.
@endphp

<style>
    .glass-panel {
        @apply bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl shadow-2xl;
    }

    .nav-link {
        @apply flex items-center px-3 py-2 rounded-xl text-[11px] text-slate-300 hover:bg-white/5 transition-colors;
    }

    .nav-link-active {
        @apply bg-cyan-500/20 text-cyan-300;
    }

    .mobile-nav-link {
        @apply text-[11px] px-2 py-1 rounded-full text-slate-300 hover:text-cyan-300;
    }

    .mobile-nav-link-active {
        @apply bg-cyan-500/20 text-cyan-300;
    }
</style>