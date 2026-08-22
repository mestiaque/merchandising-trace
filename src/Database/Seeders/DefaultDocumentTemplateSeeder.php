<?php

namespace ME\MerchandisingTrace\Database\Seeders;

use Illuminate\Database\Seeder;
use ME\MerchandisingTrace\Models\DocumentTemplate;

/**
 * §M13 — the global default checklist used when a buyer has no
 * buyer-specific template of its own.
 */
class DefaultDocumentTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $template = DocumentTemplate::firstOrCreate(
            ['is_default' => true],
            ['name' => 'Default Export Document Checklist', 'is_active' => true]
        );

        if ($template->items()->exists()) {
            return;
        }

        $rows = [
            ['Commercial Invoice', 5, true],
            ['Packing List', 5, true],
            ['Bill of Lading / AWB', 7, true],
            ['Certificate of Origin', 7, true],
            ['GSP Form A', 10, false],
            ['Beneficiary Certificate', 7, false],
            ['Inspection Certificate', 3, true],
            ['Insurance Certificate', 7, false],
        ];

        foreach ($rows as $i => [$name, $offset, $mandatory]) {
            $template->items()->create([
                'name' => $name,
                'due_offset_days' => $offset,
                'is_mandatory' => $mandatory,
                'sequence' => $i + 1,
            ]);
        }
    }
}
