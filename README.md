# Laravel Search Query Builder
A `Illuminate\Database\Query\Builder` macro to easily search on multiple database columns.

## Requirements
Laravel Search Query Builder requires PHP 8+ and Laravel 9+.

## Installation
You can install the package via composer:

```
composer require djl997/laravel-search-query-builder
```

## Usage

### Basic search term
The most basic call to the `search` method requires two arguments. The first argument is the array of columnnames. The second argument is the value to compare against the columns.

For example, the following query retrieves users where the value of the `name` column or the value of the `bio` column is like "Cesar". 

```php
$search = 'Cesar';

User::search(['name', 'bio'], $search)->get();
```

This will generate a query where "Cesar" is LIKE the `name` or LIKE `bio` column. Also, the search term is wrapped between `%`.

### Multiple search terms
You may also pass multiple search terms in the query. Make sure users will add a `,` between each term. 

```php
$search = 'Cesar, Victoria';

User::search(['name', 'bio'], $search)->get();
```

This will generate a query for each part of the search term, exploded on the `,`. Exploding on a space is not possible since you want to be able to search for multi word values.

## Search multiple relationships
As you might know, the `whereHas` method gives you a lot of power to define additional query constraints. Since the `search` method is a query builder macro, you can use it here as well.

For example, the following query will return all users with "Laravel is cool" in their bios, and all users who write posts that include this phrase in the title or body.

```php
$search = 'Laravel is cool';

User::search(['bio'], $search)
    ->orWhereHas('posts', function($query) use ($search) {
        $query->search(['title', 'body']);
    })
    ->get();
```

### Combine search with other queries
As stated earlier, the `search` method is developed to extend the `Illuminate\Database\Query\Builder` and thus can be used inline with any other Builder methods such as `whereIn`, `withTrashed` or `orderBy`.
```php
$search = 'Laravel is cool';

User::whereIn('role', ['author', 'reviewer'])
    ->search(['bio'], $search)
    ->orWhereHas('posts', function($query) use ($search) {
        $query
            ->search(['title', 'body'])
            ->whereNotNull('published_at');
    })
    ->orWhereHas('comments', function($query) use ($search) {
        $query->search(['body']);
    })
    ->withTrashed()
    ->orderBy('name')
    ->get();
```
This will return a ordered list of users (deleted AND non-deleted), with "Laravel is cool" in their bios, published posts or comments.


## Changelog
Please see the GitHub releases for more information on what has changed recently.

## Contributing

Contributions or ideas are welcome.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.