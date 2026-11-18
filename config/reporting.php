<?php

return [
    'timezone' => env('REPORTING_TIMEZONE', 'Africa/Kampala'),
    'csv_exports_enabled' => env('REPORT_CSV_EXPORTS_ENABLED', true),
    'classification' => 'Restricted — authorized operational use',
    'maximum_range_days' => 366,
];
