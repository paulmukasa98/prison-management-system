<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;
use Illuminate\Contracts\Encryption\DecryptException;

class CreateDatabaseBackup extends Command
{
    protected $signature = 'pms:backup {--database= : Connection to back up; defaults to the application connection}';

    protected $description = 'Create a bounded encrypted MySQL backup in private storage';

    public function handle(BackupService $backups): int
    {
        try {
            $filename = $backups->create($this->option('database') ?: config('database.default'));
            $this->info('Encrypted backup created: '.$filename);

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $this->error($exception instanceof DecryptException ? 'Backup encryption verification failed.' : $exception->getMessage());

            return self::FAILURE;
        }
    }
}
