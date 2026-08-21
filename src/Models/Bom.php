<?php

namespace ME\MerchandisingTrace\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use ME\MerchandisingTrace\Database\Factories\BomFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bom extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'mer_boms';

    protected $fillable = ['style_id', 'version', 'status', 'remarks', 'created_by'];

    protected $casts = [
        'version' => 'integer',
    ];

    public function style(): BelongsTo
    {
        return $this->belongsTo(Style::class, 'style_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BomItem::class, 'bom_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Clones this version's items into a brand-new draft version for the
     * same style, leaving this row untouched — see the migration's docblock
     * for why versions are immutable snapshots rather than editable history.
     */
    public function cloneAsNewVersion(): self
    {
        $next = static::where('style_id', $this->style_id)->max('version') + 1;

        $new = static::create([
            'style_id'   => $this->style_id,
            'version'    => $next,
            'status'     => 'draft',
            'remarks'    => $this->remarks,
            'created_by' => auth()->id(),
        ]);

        foreach ($this->items as $item) {
            $new->items()->create([
                'item_type'     => $item->item_type,
                'material_name' => $item->material_name,
                'unit_id'       => $item->unit_id,
                'consumption'   => $item->consumption,
                'waste_percent' => $item->waste_percent,
                'remarks'       => $item->remarks,
            ]);
        }

        return $new;
    }

    protected static function newFactory()
    {
        return BomFactory::new();
    }
}
