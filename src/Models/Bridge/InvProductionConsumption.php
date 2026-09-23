<?php

namespace ME\MerchandisingTrace\Models\Bridge;

use Illuminate\Database\Eloquent\Model;

/**
 * Read-only proxy onto the sfl-inventory package's
 * inv_production_consumptions — linked to Merchandising via its
 * mer_style_id / mer_sales_contract_po_id / mer_buyer_id soft-reference
 * columns. Never written to from this side.
 */
class InvProductionConsumption extends Model
{
    protected $table = 'inv_production_consumptions';

    protected $casts = [
        'consumption_date' => 'date',
    ];
}
