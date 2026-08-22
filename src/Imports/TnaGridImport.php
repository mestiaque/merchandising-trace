<?php

namespace ME\MerchandisingTrace\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

/**
 * Captures the raw sheet (header row + data rows) exported by
 * TnaGridExportService — TnaGridImportService does the actual matching/
 * update work so it stays testable without touching a real Excel file.
 */
class TnaGridImport implements ToArray
{
    public function array(array $array): void
    {
        //
    }
}
