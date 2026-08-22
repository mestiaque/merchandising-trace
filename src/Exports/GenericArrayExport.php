<?php

namespace ME\MerchandisingTrace\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * One reusable Excel export for every §M15 report — ReportService already
 * shapes each report into a plain ['headers' => [...], 'rows' => [...]]
 * array, so a single generic exporter covers all fourteen.
 */
class GenericArrayExport implements FromArray, WithHeadings
{
    public function __construct(
        private readonly array $headers,
        private readonly array $rows,
    ) {
    }

    public function array(): array
    {
        return array_map(fn ($row) => array_values($row), $this->rows);
    }

    public function headings(): array
    {
        return $this->headers;
    }
}
