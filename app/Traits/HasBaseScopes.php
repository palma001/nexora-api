<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasBaseScopes
{
    /**
     * Apply a set of filters to the query.
     * Expects an array or collection of filters.
     * 
     * @param Builder $query
     * @param array $filters
     * @return Builder
     */
    public function scopeFilters(Builder $query, array $filters): Builder
    {
        \Log::info('Filters received for ' . get_class($this) . ':', $filters);
        $ignore = ['page', 'per_page'];

        foreach ($filters as $field => $value) {
            if (in_array($field, $ignore)) continue;
            if (empty($value) && $value !== '0') continue;

            if (method_exists($this, 'scope' . ucfirst($field))) {
                $query->{$field}($value);
            } elseif ($field === 'search') {
                $query->search($value);
            } elseif ($field === 'sort') {
                $query->sort($value);
            } else {
                $query->where($field, $value);
            }
        }

        return $query;
    }

    /**
     * Search for a term in specific columns.
     * 
     * @param Builder $query
     * @param string $term
     * @return Builder
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        if (empty($term)) return $query;

        $searchable = property_exists($this, 'searchable') ? $this->searchable : [];

        if (empty($searchable)) return $query;

        return $query->where(function ($q) use ($term, $searchable) {
            foreach ($searchable as $column) {
                if (str_contains($column, '.')) {
                    $parts = explode('.', $column);
                    $field = array_pop($parts);
                    $relation = implode('.', $parts);

                    $q->orWhereHas($relation, function ($relQuery) use ($field, $term) {
                        $relQuery->where($field, 'like', "%{$term}%");
                    });
                } else {
                    $q->orWhere($column, 'like', "%{$term}%");
                }
            }
        });
    }

    /**
     * Sort the query by given parameters.
     * format: column,direction or column
     * 
     * @param Builder $query
     * @param string $sort
     * @return Builder
     */
    public function scopeSort(Builder $query, string $sort): Builder
    {
        if (empty($sort)) return $query;

        $parts = explode(',', $sort);
        $column = $parts[0];
        $direction = $parts[1] ?? 'asc';

        return $query->orderBy($column, $direction);
    }

    /**
     * Filter by date range.
     * 
     * @param Builder $query
     * @param string $field
     * @param array $dates [start_date, end_date]
     * @return Builder
     */
    public function scopeBetween(Builder $query, string $field, array $dates): Builder
    {
        if (count($dates) >= 2) {
            return $query->whereDate($field, '>=', $dates[0])
                         ->whereDate($field, '<=', $dates[1]);
        }
        return $query;
    }

    /**
     * Filter where field is in array.
     * 
     * @param Builder $query
     * @param string $field
     * @param array $values
     * @return Builder
     */
    public function scopeWhereInValues(Builder $query, string $field, array $values): Builder
    {
        return $query->whereIn($field, $values);
    }
    
    /**
     * Filter where field is NOT in array.
     * 
     * @param Builder $query
     * @param string $field
     * @param array $values
     * @return Builder
     */
    public function scopeWhereNotInValues(Builder $query, string $field, array $values): Builder
    {
        return $query->whereNotIn($field, $values);
    }

    /**
     * Filter where field equals value.
     * 
     * @param Builder $query
     * @param string $field
     * @param mixed $value
     * @return Builder
     */
    public function scopeWhereEqual(Builder $query, string $field, mixed $value): Builder
    {
        return $query->where($field, '=', $value);
    }

    /**
     * Filter where field does not equal value.
     * 
     * @param Builder $query
     * @param string $field
     * @param mixed $value
     * @return Builder
     */
    public function scopeWhereNotEqual(Builder $query, string $field, mixed $value): Builder
    {
        return $query->where($field, '!=', $value);
    }
}
