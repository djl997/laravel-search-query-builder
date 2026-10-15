<?php

namespace Djl997\LaravelSearchQueryBuilder;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Str;

class LaravelSearchQueryBuilderServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Builder::macro('search', function (array $fields, string $queryString, string $separator = ',') {
            // Strip each term of unnecessary spaces and drop empty terms, so "foo," doesn't match everything
            $terms = Str::of($queryString)->lower()->explode($separator)
                ->map(fn ($term) => Str::squish($term))
                ->reject(fn ($term) => $term === '');

            // Group in where, just to be sure
            return $this->where(function (Builder $query) use ($fields, $terms) {
                foreach ($terms as $term) {
                    foreach ($fields as $field) {
                        // Foreach search term and column, add a `orWhere` query; wrap() quotes the column name
                        $query->orWhere($query->raw('LOWER('.$query->getGrammar()->wrap($field).')'), 'LIKE', '%'.$term.'%');
                    }
                }
            });
        });
    }
}
