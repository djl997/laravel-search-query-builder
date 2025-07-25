<?php

namespace Djl997\LaravelSearchQueryBuilder;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
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
        Builder::macro('search', function(array $fields, string $queryString, string $separator = ',') {
            $strings = Str::of($queryString)->squish()->explode($separator);
            
            // Group in where, just to be sure
            $this->where(function(Builder $query) use ($fields, $strings) {
                foreach($strings as $string) {
                    foreach($fields as $field) {
                        // Foreach search term and column, add a `orWhere` query
                        // Also, strip the string of unnecessary spaces at the end or beginning
                        $query->orWhere(DB::raw("LOWER($field)"), 'LIKE', '%'.Str::of($string)->squish().'%');
                    }
                }
            });
        });
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // ..
    }
}