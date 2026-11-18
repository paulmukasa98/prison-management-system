<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExportService
{
    /** @param iterable<array<array-key, mixed>> $rows */
    public function download(string $filename, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($rows): void {
            $stream = fopen('php://output', 'wb');
            fwrite($stream, "\xEF\xBB\xBF");
            foreach ($rows as $row) {
                $safe = array_map(static function (mixed $value): string {
                    $value = (string) $value;

                    return preg_match('/^[\s\p{C}]*[=+@-]/u', $value) ? "'".$value : $value;
                }, $row);
                fputcsv($stream, $safe, ',', '"', '');
            }
            fclose($stream);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
