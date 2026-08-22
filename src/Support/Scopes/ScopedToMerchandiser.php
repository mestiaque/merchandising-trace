<?php

namespace ME\MerchandisingTrace\Support\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Row-level visibility for merchandiser-owned records (Inquiry, Sample,
 * Sales Contract, ...): a plain Merchandiser only sees rows where
 * merchandiser_id = auth()->id(); merch_scope.view_all bypasses it.
 * Apply via `static::addGlobalScope(new ScopedToMerchandiser)` in the
 * model's booted() method.
 */
class ScopedToMerchandiser implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth()->user();

        if (! $user || $user->can('merch_scope.view_all')) {
            return;
        }

        $builder->where($model->getTable() . '.merchandiser_id', $user->id);
    }
}
