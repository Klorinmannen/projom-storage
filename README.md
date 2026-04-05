# JRF data storage access library
[![PHP version support][php-version-badge]][php-version]
[![CI][ci-badge]][workflow-actions]
[![PHPUnit][phpunit-coverage-badge]][workflow-actions]

[php-version-badge]: https://img.shields.io/badge/php-%5E8.2-7A86B8
[php-version]: https://www.php.net/supported-versions.php
[ci-badge]: https://github.com/Klorinmannen/jrf-storage/workflows/CI/badge.svg
[workflow-actions]: https://github.com/Klorinmannen/jrf-storage/actions
[phpunit-coverage-badge]: ./phpunit-coverage-badge.svg

### Project goals
* Accessing data stores with a simple interface.
* Should be easy and intuitive to understand and use.
* Lightweight, no dependencies.

## Installation

```bash
composer require jrf/storage
```

## Setup

```php
use JRF\Storage\Manager;

Manager::initialize([
    [
        'engine' => 'mysql',
        'connections' => [
            [
                'name'     => 'default',
                'host'     => 'localhost',
                'port'     => 3306,
                'database' => 'my_database',
                'username' => 'username',
                'password' => 'password',
            ]
        ]
    ]
]);
```

## Query builder

```php
use JRF\Storage\Database\Query;
use JRF\Storage\Database\Util\Operator;
use JRF\Storage\Database\Util\Sort;

// Select all users
$users = Query::build('User')->select();

// Select specific fields with a filter
$users = Query::build('User')
    ->filterOn('Active', 1)
    ->select('UserID', 'Name', 'Email');

// Filter with operator
$users = Query::build('User')
    ->filterOn('Age', 18, Operator::GTE)
    ->sortOn(['Name' => Sort::ASC])
    ->limit(10)
    ->select();

// Insert
$userID = Query::build('User')->insert(['Name' => 'John', 'Email' => 'john@example.com']);

// Update
Query::build('User')->filterOn('UserID', $userID)->update(['Name' => 'Jane']);

// Delete
Query::build('User')->filterOn('UserID', $userID)->delete();

// Raw SQL
$result = Query::sql('SELECT * FROM User WHERE UserID = ?', [$userID]);
```

## Repository trait

Apply the `Repository` trait to a class for a higher-level model interface. Table name and primary field are derived automatically from the class name, or can be overridden.

```php
use JRF\Storage\Database\Repository;

class User
{
    use Repository;

    // Optional overrides (defaults derived from class name)
    public static function table(): string         { return 'User'; }
    public static function primaryField(): string  { return 'UserID'; }

    // Optional output processing
    public static function formatFields(): array   { return ['UserID' => 'int', 'Active' => 'bool']; }
    public static function redactFields(): array   { return ['Password']; }
    public static function selectFields(): array   { return ['UserID', 'Name', 'Email', 'Active']; }
}

// CRUD
$userID = User::create(['Name' => 'John', 'Email' => 'john@example.com']);
$user   = User::find($userID);
User::update($userID, ['Name' => 'Jane']);
User::delete($userID);

// Queries
$users = User::all(filters: ['Active' => 1], sortOn: ['Name' => Sort::ASC]);
$user  = User::get('Email', 'john@example.com');
$users = User::search('Name', 'John');
$page  = User::paginate(page: 1, pageSize: 10, sortOn: ['Name' => Sort::ASC]);

// Aggregates
$count = User::count();
$sum   = Invoice::sum('Amount', ['Paid' => 0]);

// Escape to query builder
$users = User::query()->filterOn('Active', 1)->limit(5)->select();
```

## Transactions

```php
Query::startTransaction();
// ... queries ...
Query::endTransaction();    // commit
Query::revertTransaction(); // rollback
```

## Multiple connections

```php
// Switch active connection by name
Query::useConnection('secondary');
```
