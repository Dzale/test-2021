<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\QueryBuilder;

trait SearchTrait
{
    public function getAvailableIncludes()
    {
        return $this->availableIncludes;
    }

    public function scopeLoadIncludes(Builder $query)
    {
        $this->ensureAllIncludesExist();
        $includes = request()->includes();

        return $includes ? $this->exists ? $this->load($includes) : $query->with($includes) : $query;
    }

    public function scopeSearch(Builder $query): QueryBuilder
    {
        return QueryBuilder::for($query)
            ->allowedFilters($this->searchableColumns)
            ->allowedSorts($this->searchableColumns)
            ->allowedFields($this->visible)
            ->allowedIncludes($this->availableIncludes);
    }

    protected function ensureAllIncludesExist()
    {
        $allowedIncludes = collect($this->getAvailableIncludes());
        $includes = request()->includes();
        if (!$includes) {
            return;
        }

        $diff = collect(explode(',', $includes))->diff($allowedIncludes);

        if ($diff->count()) {
            $message = __('search.unknown_includes', [
                'includes' => $diff->implode(', ')
            ]);

            if ($allowedIncludes->count()) {
                $message .= __('search.available_includes', [
                    'includes' => $allowedIncludes->implode(', ')
                ]);
            } else {
                $message .= __('search.no_available_includes');
            }
            abort(400, $message);
        }
    }
}
