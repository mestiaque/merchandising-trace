<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A booking is raised either against the Sales Contract or against the
 * buyer's export LC. The LC is recorded on the contract (mer_sales_contracts
 * .lc_no), so both still point at sales_contract_id — this column only
 * records which document the booking was raised against.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mer_material_bookings', function (Blueprint $table) {
            $table->string('booking_against', 20)->default('sales_contract')->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('mer_material_bookings', function (Blueprint $table) {
            $table->dropColumn('booking_against');
        });
    }
};
