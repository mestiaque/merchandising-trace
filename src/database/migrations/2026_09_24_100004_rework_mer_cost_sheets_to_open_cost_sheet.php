<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Cost sheet → the factory's "Open Cost Sheet" layout, costed per DOZEN:
 *   A fabric | B trims | C wash | D stone | E print | F heat seal
 *   + CM (SMV-based) + commercial % → FOB / dozen and / piece.
 *
 * - style_id becomes optional: a sheet can be costed from an inquiry
 *   (inquiry_id) or from nothing but a free-text style_ref.
 * - Line consumption is now per dozen garments. Fabric lines (yds / pc)
 *   are ×12; other lines were "pcs per garment", which is already
 *   "dozens per dozen", so they keep their number. amount = the line's
 *   cost per dozen (fabric: cons × rate; others: cons × rate × 12).
 *
 * Every existing sheet keeps its price exactly:
 * - accessory lines move to section B (trims);
 * - the old flat inputs (accessories / wash / print-emb per piece) become
 *   one line each in B / C / E, so they show on the sheet and survive a
 *   recompute;
 * - "commercial" lines are dropped — their value is already the flat
 *   commercial_cost, which is kept (commercial_percent 0 = use the flat
 *   amount);
 * - "process" lines are dropped — the old model never priced them (the
 *   demo "CM (sewing)" line), and CM is now its own row;
 * - freight / testing / overhead / profit stay as they were and print as
 *   "Other cost" / "Profit" only when non-zero.
 * Header money columns stay per PIECE (reports read them).
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->setStyleNullable(true);

        Schema::table('mer_cost_sheets', function (Blueprint $table) {
            $table->foreignId('inquiry_id')->nullable()->after('style_id')->constrained('mer_inquiries')->nullOnDelete();
            $table->string('style_ref', 150)->nullable()->after('inquiry_id');
            $table->string('garment_description')->nullable()->after('style_ref');
            $table->string('size_range', 100)->nullable()->after('garment_description');
            $table->date('costing_date')->nullable()->after('size_range');
            $table->decimal('stone_cost', 15, 4)->default(0)->after('wash_cost');
            $table->decimal('heat_seal_cost', 15, 4)->default(0)->after('stone_cost');
            $table->decimal('commercial_percent', 6, 2)->default(0)->after('commercial_cost');
            $table->string('front_image')->nullable()->after('remarks');
            $table->string('back_image')->nullable()->after('front_image');
            $table->string('sketch_image')->nullable()->after('back_image');
        });

        Schema::table('mer_cost_sheet_items', function (Blueprint $table) {
            $table->string('supplier_name', 150)->nullable()->after('item_id');
        });

        $now = now();
        $line = fn (int $sheetId, string $group, string $description, float $perPiece) => [
            'cost_sheet_id' => $sheetId, 'group' => $group, 'description' => $description,
            'consumption' => 1, 'rate' => $perPiece, 'amount' => 0, 'created_at' => $now, 'updated_at' => $now,
        ];

        DB::table('mer_cost_sheets')->orderBy('id')->each(function ($sheet) use ($line) {
            $lines = DB::table('mer_cost_sheet_items')->where('cost_sheet_id', $sheet->id);
            $insert = [];

            if ((float) $sheet->accessories_cost > 0 && ! (clone $lines)->where('group', 'accessories')->exists()) {
                $insert[] = $line($sheet->id, 'trims', 'Accessories', (float) $sheet->accessories_cost);
            }
            if ((float) $sheet->wash_cost > 0) {
                $insert[] = $line($sheet->id, 'wash', 'Wash', (float) $sheet->wash_cost);
            }
            if ((float) $sheet->print_emb_cost > 0) {
                $insert[] = $line($sheet->id, 'print', 'Print / Embellishment', (float) $sheet->print_emb_cost);
            }
            if ($insert) {
                DB::table('mer_cost_sheet_items')->insert($insert);
            }

            DB::table('mer_cost_sheets')->where('id', $sheet->id)->update([
                'style_ref' => DB::table('mer_styles')->where('id', $sheet->style_id)->value('style_no'),
                // Accessories are part of section B now.
                'trims_cost' => (float) $sheet->trims_cost + (float) $sheet->accessories_cost,
                'accessories_cost' => 0,
            ]);
        });

        DB::table('mer_cost_sheet_items')->whereIn('group', ['commercial', 'process'])->delete();
        DB::table('mer_cost_sheet_items')->where('group', 'accessories')->update(['group' => 'trims']);
        DB::table('mer_cost_sheet_items')->where('group', 'fabric')->update(['consumption' => DB::raw('consumption * 12')]);
        DB::table('mer_cost_sheet_items')->update([
            'amount' => DB::raw("COALESCE(consumption, 0) * rate * (CASE WHEN `group` = 'fabric' THEN 1 ELSE 12 END)"),
        ]);
    }

    /**
     * Schema and line basis are reversed; lines this migration generated or
     * dropped are not restored (they are folded into trims / wash / print).
     */
    public function down(): void
    {
        Schema::table('mer_cost_sheet_items', function (Blueprint $table) {
            $table->dropColumn('supplier_name');
        });

        DB::table('mer_cost_sheet_items')->where('group', 'fabric')->update(['consumption' => DB::raw('consumption / 12')]);
        DB::table('mer_cost_sheet_items')->whereIn('group', ['wash', 'stone', 'print', 'heat_seal'])->update(['group' => 'process']);
        DB::table('mer_cost_sheet_items')->update(['amount' => DB::raw('COALESCE(consumption, 0) * rate')]);

        Schema::table('mer_cost_sheets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('inquiry_id');
            $table->dropColumn([
                'style_ref', 'garment_description', 'size_range', 'costing_date', 'stone_cost', 'heat_seal_cost',
                'commercial_percent', 'front_image', 'back_image', 'sketch_image',
            ]);
        });

        // Sheets without a style can't go back under a NOT NULL style_id.
        if (DB::table('mer_cost_sheets')->whereNull('style_id')->doesntExist()) {
            $this->setStyleNullable(false);
        }
    }

    /** ALTER … MODIFY on MySQL/MariaDB (no doctrine/dbal needed); ->change() elsewhere. */
    private function setStyleNullable(bool $nullable): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE `mer_cost_sheets` MODIFY `style_id` BIGINT UNSIGNED ' . ($nullable ? 'NULL' : 'NOT NULL'));

            return;
        }

        Schema::table('mer_cost_sheets', function (Blueprint $table) use ($nullable) {
            $table->unsignedBigInteger('style_id')->nullable($nullable)->change();
        });
    }
};
