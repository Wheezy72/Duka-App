@extends('layouts.app')

@section('content')
    <div
        class="min-h-[calc(100vh-4rem)] flex items-center justify-center px-4 md:px-0"
        x-data="{ mode: '{{ request()->routeIs('register') ? 'register' : 'login' }}' }"
    >
        <div class="max-w-md w-full glass-panel px-6 py-6 md:px-8 md:py-7 relative overflow-hidden">
            <div class="absolute -top-24 -right-24 h-48 w-48 rounded-full bg-cyan-500/20 blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-32 -left-16 h-56 w-56 rounded-full bg-purple-500/20 blur-3xl pointer-events-none"></div>

            <div class="relative z-10 space-y-5">
                <div class="text-center space-y-1">
                    <div class="inline-flex items-center justify-center rounded-2xl bg-white/10 px-3 py-1 mb-2 shadow-lg shadow-black/40">
                        <div class="h-7 w-7 rounded-xl bg-cyan-500/90 flex items-center justify-center mr-2">
                            <span class="text-[10px] font-bold tracking-widest text-slate-950">DA</span>
                        </div>
                        <span class="text-[11px] font-medium tracking-[0.18em] text-slate-300">DUKA-APP</span>
                    </div>
                    <h1 class="text-xl font-semibold tracking-tight text-slate-50">
                        <span x-show="mode === 'login'">Welcome back</span>
                        <span x-show="mode === 'register'">Create your shop account</span>
                    </h1>
                    <p class="text-[11px] text-slate-400">
                        <span x-show="mode === 'login'">Sign in to continue to your Midnight Glass POS.</span>
                        <span x-show="mode === 'register'">Register and start selling in a few seconds.</span>
                    </p>
                </div>

                <!-- Toggle -->
                <div class="relative flex items-center justify-between rounded-xl bg-white/5 p-1 border border-white/10">
                    <button
                        type="button"
                        @click="mode = 'login'"
                        class="flex-1 text-center text-[11px] font-medium py-1.5 rounded-lg transition-all duration-200"
                        :class="mode === 'login' ? 'bg-cyan-500 text-slate-950 shadow-lg shadow-cyan-500/40' : 'text-slate-400 hover:text-slate-200'"
                    >
                        Sign In
                    </button>
                    <button
                        type="button"
                        @click="mode = 'register'"
                        class="flex-1 text-center text-[11px] font-medium py-1.5 rounded-lg transition-all duration-200"
                        :class="mode === 'register' ? 'bg-cyan-500 text-slate-950 shadow-lg shadow-cyan-500/40' : 'text-slate-400 hover:text-slate-200'"
                    >
                        Register
                    </button>
                </div>

                <!-- Forms -->
                <div class="space-y-4">
                    <!-- Login form -->
                    <form
                        x-show="mode === 'login'"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-2"
                        method="POST"
                        action="{{ route('login') }}"
                        class="space-y-3"
                    >
                        @csrf

                        <div class="space-y-1.5">
                            <label class="text-[11px] text-slate-300">Email</label>
                            <input
                                type="email"
                                name="email"
                                required
                                autofocus
                                autocomplete="email"
                                class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs text-slate-100 placeholder:text-slate-500 focus:border-cyan-400 focus:outline-none focus:ring-0"
                                placeholder="you@dukastore.co.ke"
                            >
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[11px] text-slate-300">Password</label>
                            <input
                                type="password"
                                name="password"
                                required
                                autocomplete="current-password"
                                class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs text-slate-100 placeholder:text-slate-500 focus:border-cyan-400 focus:outline-none focus:ring-0"
                                placeholder="••••••••"
                            >
                        </div>

                        <div class="flex items-center justify-between text-[11px] text-slate-400">
                            <label class="inline-flex items-center space-x-2">
                                <input
                                    type="checkbox"
                                    name="remember"
                                    class="h-3.5 w-3.5 rounded border-white/20 bg-white/5 text-cyan-500 focus:ring-0"
                                >
                                <span>Remember me</span>
                            </label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-cyan-300 hover:text-cyan-200">
                                    Forgot password?
                                </a>
                            @endif
                        </div>

                        <button
                            type="submit"
                            class="w-full inline-flex items-center justify-center rounded-2xl bg-cyan-500 px-4 py-2.5 text-xs font-semibold tracking-tight text-slate-950 shadow-lg shadow-cyan-500/40 transition-transform duration-150 hover:bg-cyan-400 active:scale-95"
                        >
                            Sign In
                        </button>
                    </form>

                    <!-- Register form -->
                    <form
                        x-show="mode === 'register'"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-2"
                        method="POST"
                        action="{{ route('register') }}"
                        class="space-y-3"
                    >
                        @csrf

                        <div class="space-y-1.5">
                            <label class="text-[11px] text-slate-300">Full name</label>
                            <input
                                type="text"
                                name="name"
                                required
                                autocomplete="name"
                                class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs text-slate-100 placeholder:text-slate-500 focus:border-cyan-400 focus:outline-none focus:ring-0"
                                placeholder="Achieng' Wa Duka"
                            >
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[11px] text-slate-300">Email</label>
                            <input
                                type="email"
                                name="email"
                                required
                                autocomplete="email"
                                class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs text-slate-100 placeholder:text-slate-500 focus:border-cyan-400 focus:outline-none focus:ring-0"
                                placeholder="you@dukastore.co.ke"
                            >
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div class="space-y-1.5">
                                <label class="text-[11px] text-slate-300">Password</label>
                                <input
                                    type="password"
                                    name="password"
                                    required
                                    autocomplete="new-password"
                                    class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs text-slate-100 placeholder:text-slate-500 focus:border-cyan-400 focus:outline-none focus:ring-0"
                                    placeholder="••••••••"
                                >
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[11px] text-slate-300">Confirm</label>
                                <input
                                    type="password"
                                    name="password_confirmation"
                                    required
                                    autocomplete="new-password"
                                    class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs text-slate-100 placeholder:text-slate-500 focus:border-cyan-400 focus:outline-none focus:ring-0"
                                    placeholder="••••••••"
                                >
                            </div>
                        </div>

                        <button
                            type="submit"
                            class="w-full inline-flex items-center justify-center rounded-2xl bg-cyan-500 px-4 py-2.5 text-xs font-semibold tracking-tight text-slate-950 shadow-lg shadow-cyan-500/40 transition-transform duration-150 hover:bg-cyan-400 active:scale-95"
                        >
                            Create account
                        </button>
                    </form>

                    <!-- Divider -->
                    <div class="flex items-center space-x-3 text-[10px] text-slate-500">
                        <div class="h-px flex-1 bg-gradient-to-r from-transparent via-white/20 to-transparent"></div>
                        <span>or continue with</span>
                        <div class="h-px flex-1 bg-gradient-to-r from-transparent via-white/20 to-transparent"></div>
                    </div>

                    <!-- Google button -->
                    <a
                        href="{{ route('auth.google.redirect') }}"
                        class="w-full inline-flex items-center justify-center space-x-2 rounded-2xl bg-white/5 px-4 py-2.5 text-xs font-semibold tracking-tight text-slate-50 border border-white/10 shadow-lg shadow-black/40 transition-transform duration-150 hover:bg-white/10 active:scale-95"
                    >
                        <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-white">
                            <span class="text-[10px] font-bold text-slate-900">G</span>
                        </span>
                        <span>Continue with Google</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection