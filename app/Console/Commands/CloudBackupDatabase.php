<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CloudBackupDatabase extends Command
{
    protected $signature = 'duka:backup-cloud 
                            {--disk=s3 : The filesystem disk to use for backup}
                            {--path=backups/database : Base path on the disk where backups are stored}';

    protected $description = 'Upload the local SQLite database file to cloud storage with a timestamped name';

    public function handle(): int
    {
        $databasePath = database_path('database.sqlite');

        if (! file_exists($databasePath)) {
            $this->error('database.sqlite not found. Make sure your SQLite database exists at database/database.sqlite.');
            return self::FAILURE;
        }

        $diskName = (string) $this->option('disk');
        $basePath = rtrim((string) $this->option('path'), '/');

        $disk = Storage::disk($diskName);

        $timestamp = now()->format('Y-m-d_H-i-s');
        $fileName = "duka-{$timestamp}.sqlite";
        $remotePath = "{$basePath}/{$fileName}";

        $this->info("Uploading {$databasePath} to [{$diskName}] at [{$remotePath}]...");

        $handle = fopen($databasePath, 'rb');

        try {
            $disk->put($remotePath, $handle);
        } finally {
            if (is_resource($handle)) {
                fclose($handle);
            }
        }

        $this->info('Backup uploaded successfully.');

        return self::SUCCESS;
    }
}