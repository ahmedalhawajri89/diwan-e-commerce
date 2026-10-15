<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * A list, downloaded as the operator sees it: the same search, filter and
 * sort, every page of it, as CSV.
 *
 * Streamed in chunks so a long list never sits in memory whole. Starts with
 * a UTF-8 byte-order mark: without it Excel reads the file as the machine's
 * legacy code page and every Arabic name arrives as mojibake.
 */
final class CsvExport
{
    /**
     * @param  Builder  $query  ordered as the list is
     * @param  array<string, callable>  $columns  heading => fn ($row) => value
     */
    public static function download(string $name, Builder $query, array $columns): StreamedResponse
    {
        $filename = $name.'-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($query, $columns) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, array_keys($columns));

            // chunkById would re-order by id and lose the list's own sort.
            $query->chunk(500, function ($rows) use ($out, $columns) {
                foreach ($rows as $row) {
                    fputcsv($out, array_map(fn ($cell) => self::safe($cell($row)), $columns));
                }
            });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * A cell a spreadsheet will not run. A value beginning with = + - @ is a
     * formula to Excel; a customer who names themselves "=HYPERLINK(…)"
     * must arrive as text.
     */
    private static function safe(mixed $value): string
    {
        $value = (string) ($value ?? '');

        return preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }
}
