<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class B1DatabaseGuard
{
    public function assertAllowed(string $connection, bool $disposable = false): void
    {
        $databases = ['b1_dev' => 'ups_pms_b1_dev', 'b1_test' => 'ups_pms_b1_test'];
        $settings = config("database.connections.{$connection}");

        if (! app()->environment('local', 'testing')
            || ! isset($databases[$connection])
            || ($disposable && ($connection !== 'b1_test' || ! app()->environment('testing')))
            || ! is_array($settings)
            || ($settings['driver'] ?? null) !== 'mysql'
            || ($settings['host'] ?? null) !== '127.0.0.1'
            || (string) ($settings['port'] ?? '') !== '3311'
            || ($settings['database'] ?? null) !== $databases[$connection]
            || ! empty($settings['url'])
            || ! empty($settings['unix_socket'])) {
            throw new RuntimeException('B1 writes require an explicitly isolated local database; resets require b1_test in testing.');
        }

        $server = DB::connection($connection)->selectOne('SELECT DATABASE() AS database_name, @@port AS server_port, @@datadir AS data_directory');
        $expectedDirectory = strtolower(str_replace('\\', '/', storage_path('app/private/b1-mysql')));
        $actualDirectory = strtolower(rtrim(str_replace('\\', '/', $server->data_directory), '/'));

        if ($server->database_name !== $databases[$connection]
            || (int) $server->server_port !== 3311
            || $actualDirectory !== $expectedDirectory) {
            throw new RuntimeException('B1 database identity does not match the disposable local instance.');
        }
    }
}
