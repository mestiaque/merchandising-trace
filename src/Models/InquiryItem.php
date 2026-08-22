<?php

namespace ME\MerchandisingTrace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InquiryItem extends Model
{
    protected $table = 'mer_inquiry_items';

    protected $fillable = ['inquiry_id', 'style_ref', 'product_type_id', 'color_ref', 'qty', 'target_price', 'remarks'];

    protected $casts = ['target_price' => 'decimal:4'];

    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(Inquiry::class, 'inquiry_id');
    }

    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class, 'product_type_id');
    }
}
