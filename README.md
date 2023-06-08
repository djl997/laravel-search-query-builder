# Laravel Search Query Builder

[![Latest Version on Packagist](https://img.shields.io/packagist/v/djl997/laravel-search-query-builder.svg?style=flat-square)](https://packagist.org/packages/djl997/laravel-search-query-builder)
[![Total Downloads](https://img.shields.io/packagist/dt/djl997/laravel-search-query-builder.svg?style=flat-square)](https://packagist.org/packages/djl997/laravel-search-query-builder)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

Laravel Search Query Builder is a `Illuminate\Database\Query\Builder` macro to easily search on multiple database columns and model relationships.

Let's imagine a case where you want to return all users with _something_ in their `name` or `bio` field.

```php
$search = 'Cesar';

// vanilla laravel
User::where('name', 'LIKE', '%'. $search .'%')->orWhere('bio', 'LIKE', '%'. $search .'%')->get();

// laravel with laravel-search-query-builder package
User::search(['name', 'bio'], $search)->get();
```

This example is not very difficult to achieve in Laravel, but it can grow very quickly, especially if you also want to search in model relationships like posts or comments for example.

Let's see how the Laravel Search Query Builder package can improve this situation.

## Requirements
Laravel Search Query Builder requires PHP 8+ and Laravel 9+.

## Installation
You can install the package via composer:

```
composer require djl997/laravel-search-query-builder
```

## Usage

### Basic search term
The most basic call to the `search` method requires two arguments: 
1. an array of columns,
2. the search value.

For example, the following query retrieves users where the `name` column or the value of the `bio` column is _like_ "Cesar". 

```php
$search = 'Cesar';

User::search(['name', 'bio'], $search)->get();
```

This will generate a query where "Cesar" is LIKE the `name` or LIKE `bio` column. For clarification, the search term is wrapped between `%...%` to check if (a part of) the string is present.

### Multiple search terms
You can also perform multiple searches at once by separating them with a komma. 

```php
$search = 'Cesar, Victoria';

User::search(['name', 'bio'], $search)->get();
```

This will generate a query for each part of the search term, exploded on a `,` by default.

#### Change the separator
If you want to change the delimiter, you can do so by changing the third argument.

```php
User::search(['name', 'bio'], 'Cesar Victoria', '|')->get();
```
This will generate a query for each part of the search term, exploded on a `|`. 

### Search multiple relationships
As you probably know, the `whereHas` method gives you a lot of power to define additional query constraints. And you guessed it, you can use the `search` method here as well.

For example, the following query will return all users with "Laravel is cool" in their bios + all users who write posts that include this phrase in the title or body.

```php
$search = 'Laravel is cool';

User::search(['bio'], $search)
    ->orWhereHas('posts', function($query) use ($search) {
        $query->search(['title', 'body']);
    })
    ->get();
```


### Combine search with other queries
Since the `search` method is developed to be a macro and extend the `Illuminate\Database\Query\Builder` it can be used inline with any other Builder methods such as `whereIn`, `withTrashed` or `orderBy`, or your very own macros.
```php
$search = 'Laravel is cool';

User::whereIn('role', ['author', 'reviewer'])
    ->where(function($query) { //see note below
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
> **Note!** The search query is wrapped in a where query, because you most likely don't want the 'orWheres' to bypass any other filters.  

### Dynamic columns search
This is a short-sighted example of how you can also make the columns dynamic:

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