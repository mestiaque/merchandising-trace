<?php

namespace ME\MerchandisingTrace\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TnaTask extends Model
{
    protected $table = 'mer_tna_tasks';

    public const STATUSES = ['pending', 'in_progress', 'done', 'approved', 'na', 'delayed'];

    protected $fillable = [
        'tna_plan_id', 'tna_template_task_id', 'group_name', 'task_code', 'task_name', 'value_type', 'sequence',
        'plan_date', 'revised_date', 'actual_date', 'value_text', 'value_number', 'status', 'is_mandatory',
        'blocks_pcd', 'responsible_dept_id', 'responsible_person_id', 'remarks', 'attachment', 'is_auto',
    ];

    protected $casts = [
        'plan_date' => 'date',
        'revised_date' => 'date',
        'actual_date' => 'date',
        'value_number' => 'decimal:4',
        'is_mandatory' => 'boolean',
        'blocks_pcd' => 'boolean',
        'is_auto' => 'boolean',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(TnaPlan::class, 'tna_plan_id');
    }

    public function responsibleDept(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'responsible_dept_id');
    }

    public function responsiblePerson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_person_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(TnaTaskLog::class, 'tna_task_id')->latest('changed_at');
    }

    /**
     * §6 Rule: effective plan date for this task — the revised date if set,
     * else the original plan date.
     */
    public function effectiveDate(): ?\Illuminate\Support\Carbon
    {
        return $this->revised_date ?? $this->plan_date;
    }

    public function daysLate(): ?int
    {
        $due = $this->effectiveDate();
        if (! $due || ! $this->actual_date) {
            return null;
        }

        return $due->diffInDays($this->actual_date, false);
    }

    /**
     * §8.3 colour coding, computed (never stored so it can't go stale):
     * green = done on/before plan, amber = due within N days, red = overdue,
     * grey = N/A, blue = auto-filled (read-only).
     */
    public function boardColor(int $dueSoonDays = 3): string
    {
        if ($this->status === 'na') {
            return 'grey';
        }
        if ($this->is_auto) {
            return 'blue';
        }
        if (in_array($this->status, ['done', 'approved'], true)) {
            return 'green';
        }
        if ($this->isOverdue()) {
            return 'red';
        }
        if ($this->isDueSoon($dueSoonDays)) {
            return 'amber';
        }

        return 'default';
    }

    public function isOverdue(): bool
    {
        $due = $this->effectiveDate();

        return $due !== null
            && ! in_array($this->status, ['done', 'approved', 'na'], true)
            && $due->isPast();
    }

    public function isDueSoon(int $days = 3): bool
    {
        $due = $this->effectiveDate();

        return $due !== null
            && ! in_array($this->status, ['done', 'approved', 'na'], true)
            && ! $due->isPast()
            && now()->diffInDays($due, false) <= $days;
    }
}
