# Laravel Search Query Builder

[![Latest Version on Packagist](https://img.shields.io/packagist/v/djl997/laravel-search-query-builder.svg?style=flat-square)](https://packagist.org/packages/djl997/laravel-search-query-builder)
[![Total Downloads](https://img.shields.io/packagist/dt/djl997/laravel-search-query-builder.svg?style=flat-square)](https://packagist.org/packages/djl997/laravel-search-query-builder)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

A `Illuminate\Database\Query\Builder` macro to easily search on multiple database columns.

Let's imagine a case where you want to return all users with _something_ in their `name` or `bio` field.

```php
$search = 'Cesar';

// vanilla laravel
User::where('name', 'LIKE', '%'. $search .'%')->orWhere('bio', 'LIKE', '%'. $search .'%')->get();

// laravel with laravel-search-query-builder package
User::search(['name', 'bio'], $search)->get();
```

It's not very difficult in Laravel but it can become very long very quickly, especially if you also want to search the users posts and comments for example.

Let's see how this package can improve this situation.

## Requirements
Laravel Search Query Builder requires PHP 8+ and Laravel 9+.

## Installation
You can install the package via composer:

```
composer require djl997/laravel-search-query-builder
```

## Usage

### Basic search term
The most basic call to the `search` method requires two arguments. The first argument is the array of column names. The second argument is the value to compare against the columns.

For example, the following query retrieves users where the value of the `name` column or the value of the `bio` column is like "Cesar". 

```php
$search = 'Cesar';

User::search(['name', 'bio'], $search)->get();
```

This will generate a query where "Cesar" is LIKE the `name` or LIKE `bio` column. Also, the search term is wrapped between `%` to check (a part of) the string is present in the records.

### Multiple search terms
You can also pass multiple search terms in the search. User testing showed that exploding on spaces leads to an unwanted number of results in most cases. To reach multiple search terms, users must add a `,` between each term in the search input.

```php
$search = 'Cesar, Victoria';

User::search(['name', 'bio'], $search)->get();
```

This will generate a query for each part of the search term, exploded by default on the `,`. 

#### Change the separator
If you want to change the delimiter, you can do so by changing the third argument.

```php
User::search(['name', 'bio'], 'Cesar Victoria', ' ')->get();
```
This will generate a query for each part of the search term, exploded on the third argument. 

### Search multiple relationships
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
    ->where(function($query) { //IMPORTANT!
        ->search(['bio'], $search)
        ->orWhereHas('posts', function($query) use ($search) {
            $query
                ->search(['title', 'body'])
                ->whereNotNull('published_at');
        })
        ->orWhereHas('comments', function($query) use ($search) {
            $query->search(['body']);
        })
    })
    ->withTrashed()
    ->orderBy('name')
    ->get();
```
This will return a ordered list of users with author-, or reviewer roles (deleted AND non-deleted), with "Laravel is cool" in their bios, published posts or comments, ordered by name.
> Note that the search query is wrapped in a where query, because you most likely don't want the search to bypass the other filters. 

### Dynamic columns search
You probably already figured it out, but still: you can of course also make the columns dynamic.

```html
    <input type="text" name="search" value="Laravel is cool"/>
    <input type="checkbox" name="userColumns[]" value="name"/>
    <input type="checkbox" name="userColumns[]" value="bio"/>
```

```php
$search = $request->input('search');
$columns = $request->input('userColumns');

User::search($columns, $search)->get();
```


## Changelog
Please see the GitHub releases for more information on what has changed recently.

## Contributing

Contributions or ideas are welcome.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.