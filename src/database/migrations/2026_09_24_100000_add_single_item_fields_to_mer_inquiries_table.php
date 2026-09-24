<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * One inquiry = one item: the style ref / color that used to sit on
 * mer_inquiry_items move onto the inquiry itself. target_qty / target_price
 * are kept as columns but are now the confirmed Order Qty / Unit Price, and
 * total_value = target_qty × target_price. mer_inquiry_items is left in
 * place (not dropped) so no historical line is lost.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mer_inquiries', function (Blueprint $table) {
            $table->string('style_ref')->nullable()->after('product_type_id');
            $table->string('color_ref')->nullable()->after('style_ref');
            $table->decimal('total_value', 15, 4)->nullable()->after('target_price');
            $table->date('extended_ship_date')->nullable()->after('target_ship_date');
        });

        DB::table('mer_inquiries')->orderBy('id')->each(function ($inquiry) {
            $first = DB::table('mer_inquiry_items')->where('inquiry_id', $inquiry->id)->orderBy('id')->first();

            $qty = $inquiry->target_qty ?? $first?->qty;
            $price = $inquiry->target_price ?? $first?->target_price;

            DB::table('mer_inquiries')->where('id', $inquiry->id)->update([
                'style_ref' => $first?->style_ref,
                'color_ref' => $first?->color_ref,
                'target_qty' => $qty,
                'target_price' => $price,
                'total_value' => $qty !== null && $price !== null ? $qty * $price : null,
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('mer_inquiries', function (Blueprint $table) {
            $table->dropColumn(['style_ref', 'color_ref', 'total_value', 'extended_ship_date']);
        });
    }
};
