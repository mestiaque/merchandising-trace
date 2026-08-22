<?php

namespace ME\MerchandisingTrace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TnaTemplateTask extends Model
{
    protected $table = 'mer_tna_template_tasks';

    public const VALUE_TYPES = ['date', 'text', 'number', 'status', 'yesno'];
    public const AUTO_SOURCES = ['none', 'sample', 'material_booking', 'production'];

    protected $fillable = [
        'tna_template_id', 'group_name', 'task_code', 'task_name', 'value_type', 'sequence',
        'offset_days', 'anchor_field', 'responsible_dept_id', 'is_mandatory', 'blocks_pcd',
        'auto_source', 'auto_source_ref',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
        'blocks_pcd' => 'boolean',
        'offset_days' => 'integer',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(TnaTemplate::class, 'tna_template_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'responsible_dept_id');
    }
}
