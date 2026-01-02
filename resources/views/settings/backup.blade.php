@extends('layouts.app')

@section('page-header')
    <div class="flex items-center justify-between">
        <div>
            <p class="text-[11px] uppercase tracking-[0.18em] text-slate-400 mb-1">Settings</p>
            <h1 class="text-xl md:text-2xl font-semibold tracking-tight text-slate-50">Backup &amp; Restore</h1>
            <p class="text-xs text-slate-400 mt-1">How to protect your Duka-App data (sales, deni, and customers).</p>
        </div>
        <div class="hidden md:flex items-center space-x-2 text-[11px] text-slate-400">
            <span>Settings</span>
            <span class="text-slate-500">/</span>
            <span class="text-cyan-300">Backup</span>
        </div>
    </div>
@endsection

@section('content')
    <div class="glass-panel px-4 py-4 md:px-6 md:py-6 space-y-4 text-[12px] text-slate-200">
        <div>
            <h2 class="text-sm font-semibold tracking-tight text-slate-50 mb-1">Where is my data stored?</h2>
            <p class="text-[11px] text-slate-400">
                All your Duka-App data (sales, customers, debts) is stored in a single file called
                <span class="font-mono text-slate-200">database.sqlite</span>.
                This file lives inside your Duka-App folder in Windows.
            </p>
            <p class="mt-2 text-[11px] text-slate-400">
                Your technician can tell you the exact location (for example:
                <span class="font-mono text-slate-200">C:\Duka-App\duka-app\database\database.sqlite</span>).
                Keeping a copy of this file is the same as backing up your shop.
            </p>
        </div>

        <div class="border-t border-white/10 pt-4">
            <h2 class="text-sm font-semibold tracking-tight text-slate-50 mb-1">How to back up (recommended daily)</h2>
            <ol class="list-decimal list-inside space-y-1 text-[11px] text-slate-400">
                <li>Close the Duka-App window when you finish selling.</li>
                <li>Open <span class="font-mono text-slate-200">File Explorer</span> on your computer.</li>
                <li>Go to the folder where Duka-App is installed (ask your technician if unsure).</li>
                <li>Open the <span class="font-mono text-slate-200">database</span> folder and find
                    <span class="font-mono text-slate-200">database.sqlite</span>.
                </li>
                <li>Plug in a flash disk (USB) and create a folder on it, for example
                    <span class="font-mono text-slate-200">Duka-Backups</span>.
                </li>
                <li>Copy <span class="font-mono text-slate-200">database.sqlite</span> to the flash disk and rename the copy with today&apos;s date,
                    for example <span class="font-mono text-slate-200">duka-2025-01-05.sqlite</span>.
                </li>
            </ol>
            <p class="mt-2 text-[11px] text-emerald-300">
                Tip: Do this at the end of each day or at least once every week.
                If your computer fails, these copies will protect your business records.
            </p>
        </div>

        <div class="border-t border-white/10 pt-4">
            <h2 class="text-sm font-semibold tracking-tight text-slate-50 mb-1">How to restore from a backup</h2>
            <p class="text-[11px] text-slate-400 mb-2">
                Use these steps if your current data is damaged or you moved Duka-App to a new computer.
            </p>
            <ol class="list-decimal list-inside space-y-1 text-[11px] text-slate-400">
                <li>Close Duka-App and make sure it is not running.</li>
                <li>On your computer, go to the Duka-App <span class="font-mono text-slate-200">database</span> folder.</li>
                <li>Find the current <span class="font-mono text-slate-200">database.sqlite</span> file and rename it to keep it safe, for example
                    <span class="font-mono text-slate-200">database-old.sqlite</span>.
                </li>
                <li>Plug in your flash disk and open your <span class="font-mono text-slate-200">Duka-Backups</span> folder.</li>
                <li>Choose the most recent backup file, for example
                    <span class="font-mono text-slate-200">duka-2025-01-05.sqlite</span>, and copy it to the Duka-App
                    <span class="font-mono text-slate-200">database</span> folder.
                </li>
                <li>Rename the copied file to <span class="font-mono text-slate-200">database.sqlite</span>.</li>
                <li>Open Duka-App again. You should now see your shop data as it was on the date of that backup.</li>
            </ol>
            <p class="mt-2 text-[11px] text-rose-300">
                Note: If you restore a backup from an older date, any sales made after that date will not be in the restored file.
            </p>
        </div>

        <div class="border-t border-white/10 pt-4">
            <h2 class="text-sm font-semibold tracking-tight text-slate-50 mb-1">Monthly off-site backup (extra safety)</h2>
            <p class="text-[11px] text-slate-400">
                Once per month, copy your latest backup file to a second flash disk or to cloud storage
                (for example Google Drive) and keep it somewhere safe at home.
                This protects your shop if the computer or primary flash disk is stolen or damaged.
            </p>
        </div>
    </div>
@endsection