<?php

declare(strict_types=1);

namespace App\Console;

use App\Console\Commands\CloudBackupDatabase;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Register the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Example: nightly cloud backup at 23:30
        // $schedule->command('duka:backup-cloud')->dailyAt('23:30');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }

    /**
     * Get the commands provided by the application.
     *
     * @return array<int, class-string>
     */
    public function commandsList(): array
    {
        return [
            CloudBackupDatabase::class,
        ];
    }
}