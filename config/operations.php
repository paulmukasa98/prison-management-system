<?php

return [
    'production_approved' => env('PMS_PRODUCTION_APPROVED', true),
    'security_review_reference' => env('PMS_SECURITY_REVIEW_REFERENCE'),
    'uat_reference' => env('PMS_UAT_REFERENCE'),
    'restore_drill_reference' => env('PMS_RESTORE_DRILL_REFERENCE'),
    'offline_writes_enabled' => false,
    'backup_key' => env('PMS_BACKUP_KEY'),
    'backup_max_bytes' => 32 * 1024 * 1024,
    'backup_binary' => env('PMS_MYSQLDUMP_BINARY', 'mysqldump'),
    'restore_binary' => env('PMS_MYSQL_BINARY', 'mysql'),
    'backup_arguments' => ['--single-transaction', '--quick', '--hex-blob', '--routines', '--triggers', '--no-tablespaces', '--column-statistics=0'],
    'scheduled_backups' => env('PMS_SCHEDULED_BACKUPS', false),
];
