@extends('layouts.app')

@section('page-header')
    <div class="flex items-center justify-between">
        <div>
            <p class="text-[11px] uppercase tracking-[0.18em] text-slate-400 mb-1">Settings</p>
            <h1 class="text-xl md:text-2xl font-semibold tracking-tight text-slate-50">System Logs</h1>
            <p class="text-xs text-slate-400 mt-1">Recent errors and warnings from Duka-App (technical view).</p>
        </div>
        <div class="hidden md:flex items-center space-x-2 text-[11px] text-slate-400">
            <span>Settings</span>
            <span class="text-slate-500">/</span>
            <span class="text-cyan-300">Logs</span>
        </div>
    </div>
@endsection

@section('content')
    <div class="glass-panel px-4 py-4 md:px-6 md:py-6 space-y-3">
        <p class="text-[11px] text-slate-400">
            This page shows the last few lines of the Duka-App log file.
            Share this information with your technician when you need support.
        </p>

        <div class="rounded-xl bg-black/60 border border-white/10 max-h-[480px] overflow-auto">
            <pre class="text-[11px] leading-relaxed text-slate-200 px-4 py-3 whitespace-pre-wrap">
{{ $logs ?: 'No log entries found.' }}
            </pre>
        </div>
    </div>
@endsection