<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

class VerifyDatabaseBackup extends Command
{
    protected $signature = 'pms:backup-verify {filename} {--restore-test : Restore only into the guarded disposable b1_test database} {--confirm-database= : Must explicitly equal ups_pms_b1_test to run a restore drill}';

    protected $description = 'Verify a private encrypted backup and optionally restore it into the isolated test database';

    public function handle(BackupService $backups): int
    {
        try {
            $filename = (string) $this->argument('filename');
            $metadata = $backups->verify($filename);
            $this->info('Verified encrypted backup; source database: '.$metadata['database'].'; bytes: '.$metadata['bytes']);
            if ($this->option('restore-test')) {
                if ($this->option('confirm-database') !== 'ups_pms_b1_test') {
                    $this->error('Restore requires --confirm-database=ups_pms_b1_test and the testing environment.');

                    return self::FAILURE;
                }
                $backups->restoreToDisposableTestDatabase($filename);
                $this->info('Restore drill completed in the disposable b1_test database.');
            }

            return self::SUCCESS;
        } catch (\Throwable) {
            $this->error('Backup verification or restore failed. Check the key, file integrity, configured clients and disposable database identity.');

            return self::FAILURE;
        }
    }
}
