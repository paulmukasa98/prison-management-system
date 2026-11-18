<?php

namespace App\Services;

use App\Support\B1DatabaseGuard;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;

class BackupService
{
    public function create(string $connection): string
    {
        $settings = $this->connectionSettings($connection);
        $encrypter = $this->encrypter();
        $command = [
            config('operations.backup_binary'), ...config('operations.backup_arguments'),
            '--host='.$settings['host'], '--port='.$settings['port'], '--user='.$settings['username'],
            '--protocol=tcp', $settings['database'],
        ];
        $plain = '';
        $process = new Process($command, base_path(), ['MYSQL_PWD' => $settings['password']], timeout: 300);
        $process->run(function (string $type, string $data) use (&$plain, $process): void {
            if ($type === Process::OUT) {
                if (strlen($plain) + strlen($data) > config('operations.backup_max_bytes')) {
                    throw new RuntimeException('Backup exceeds the configured in-memory limit. Use an approved infrastructure backup service for larger databases.');
                }
                $plain .= $data;
            }
            $process->clearOutput();
        });
        if (! $process->isSuccessful()) {
            throw new RuntimeException('Database dump failed. Check the configured client binary, database connectivity and backup privileges.');
        }
        $directory = storage_path('app/private/backups');
        if (! is_dir($directory) && ! mkdir($directory, 0700, true) && ! is_dir($directory)) {
            throw new RuntimeException('Cannot create private backup storage.');
        }
        $filename = now()->format('Ymd-His').'-'.Str::uuid().'.pmsbackup';
        $path = $directory.DIRECTORY_SEPARATOR.$filename;
        $payload = json_encode(['format' => 1, 'database' => $settings['database'], 'created_at' => now()->toIso8601String(), 'sha256' => hash('sha256', $plain), 'sql' => $plain], JSON_THROW_ON_ERROR);
        if (file_put_contents($path, $encrypter->encryptString($payload), LOCK_EX) === false) {
            throw new RuntimeException('Cannot write the encrypted backup.');
        }

        return $filename;
    }

    /** @return array{database:string, created_at:string, bytes:int} */
    public function verify(string $filename): array
    {
        $payload = $this->read($filename);

        return ['database' => $payload['database'], 'created_at' => $payload['created_at'], 'bytes' => strlen($payload['sql'])];
    }

    public function restoreToDisposableTestDatabase(string $filename): void
    {
        app(B1DatabaseGuard::class)->assertAllowed('b1_test', disposable: true);
        $payload = $this->read($filename);
        $settings = $this->connectionSettings('b1_test');
        $process = new Process([
            config('operations.restore_binary'), '--host='.$settings['host'], '--port='.$settings['port'],
            '--user='.$settings['username'], '--protocol=tcp', $settings['database'],
        ], base_path(), ['MYSQL_PWD' => $settings['password']], timeout: 300);
        $process->setInput($payload['sql']);
        $process->run();
        if (! $process->isSuccessful()) {
            throw new RuntimeException('Restore failed in the disposable test database. The operational database was not modified.');
        }
    }

    /** @return array<string, mixed> */
    private function read(string $filename): array
    {
        if (basename($filename) !== $filename || ! str_ends_with($filename, '.pmsbackup')) {
            throw new RuntimeException('Supply a backup filename from private backup storage.');
        }
        $path = storage_path('app/private/backups/'.$filename);
        if (! is_file($path) || filesize($path) > config('operations.backup_max_bytes') * 4) {
            throw new RuntimeException('Backup is missing or exceeds the configured size limit.');
        }
        $payload = json_decode($this->encrypter()->decryptString(file_get_contents($path)), true, flags: JSON_THROW_ON_ERROR);
        if (($payload['format'] ?? null) !== 1 || ! is_string($payload['sql'] ?? null) || ! hash_equals($payload['sha256'] ?? '', hash('sha256', $payload['sql']))) {
            throw new RuntimeException('Backup integrity verification failed.');
        }

        return $payload;
    }

    private function encrypter(): Encrypter
    {
        $key = base64_decode((string) config('operations.backup_key'), true);
        if (! is_string($key) || strlen($key) !== 32) {
            throw new RuntimeException('Configure an independent base64-encoded 32-byte PMS_BACKUP_KEY before creating or restoring backups.');
        }

        return new Encrypter($key, 'AES-256-GCM');
    }

    /** @return array<string, mixed> */
    private function connectionSettings(string $connection): array
    {
        $settings = DB::connection($connection)->getConfig();
        if (! in_array($settings['driver'], ['mysql', 'mariadb'], true)) {
            throw new RuntimeException('Database backup requires a configured MySQL-compatible connection.');
        }

        return $settings;
    }
}
