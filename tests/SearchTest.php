<?php

namespace Djl997\LaravelSearchQueryBuilder\Tests;

use Djl997\LaravelSearchQueryBuilder\LaravelSearchQueryBuilderServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase;

class SearchTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [LaravelSearchQueryBuilderServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
    }

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function ($table) {
            $table->string('name');
            $table->string('bio')->nullable();
        });

        DB::table('users')->insert([
            ['name' => 'Cesar', 'bio' => 'Writes Laravel'],
            ['name' => 'Victoria', 'bio' => null],
            ['name' => 'Bob', 'bio' => 'Likes cats'],
        ]);
    }

    private function names(array $fields, string $search, string $separator = ','): array
    {
        return DB::table('users')->search($fields, $search, $separator)->orderBy('name')->pluck('name')->all();
    }

    public function test_it_searches_case_insensitive_on_multiple_columns()
    {
        $this->assertSame(['Cesar'], $this->names(['name', 'bio'], 'LARAVEL'));
        $this->assertSame(['Cesar'], $this->names(['name', 'bio'], 'cesar'));
    }

    public function test_it_searches_multiple_terms_with_custom_separator()
    {
        $this->assertSame(['Bob', 'Victoria'], $this->names(['name', 'bio'], 'cats, victoria'));
        $this->assertSame(['Bob', 'Victoria'], $this->names(['name', 'bio'], 'cats | victoria', '|'));
    }

    public function test_empty_terms_do_not_match_everything()
    {
        $this->assertSame(['Cesar'], $this->names(['name'], 'Cesar, '));
    }

    public function test_column_names_are_wrapped()
    {
        $sql = DB::table('users')->search(['name) or 1=1 --'], 'x')->toSql();

        $this->assertStringContainsString('LOWER("name) or 1=1 --")', $sql);
    }
}
