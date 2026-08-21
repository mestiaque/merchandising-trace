<?php

namespace ME\MerchandisingTrace\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    protected $table = 'mer_documents';

    protected $fillable = ['buyer_id', 'order_id', 'document_type', 'title', 'file_path', 'remarks', 'created_by'];

    public const DOCUMENT_TYPES = [
        'buyer_document' => 'Buyer Document',
        'tech_pack'      => 'Tech Pack',
        'po_attachment'  => 'PO Attachment',
        'artwork'        => 'Artwork',
        'approval_file'  => 'Approval File',
    ];

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class, 'buyer_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
