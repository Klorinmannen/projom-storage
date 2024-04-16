# Projom storage module
[![PHP version support][php-version-badge]][php]
[![PHPUnit][phpunit-ci-badge]][phpunit-action]

[php-version-badge]: https://img.shields.io/badge/php-%5E8.0-7A86B8
[php]: https://www.php.net/supported-versions.php
[phpunit-action]: https://github.com/Klorinmannen/projom-storage/actions
[phpunit-ci-badge]: https://github.com/Klorinmannen/projom-storage/workflows/PHPUnit/badge.svg

### The goals of this project are
* Accessing databases with a simple interface
* Should be easy to understand and use
* Lightweight
* Support for MySQL/MariaDB, SQLite and JSON

####  [Composer](https://getcomposer.org/doc/00-intro.md)

````
composer require klorinmannen/projom-storage
````

## How to use
### Static example
````
use Projom\Storage\Database\Engine;
use Projom\Storage\DB;
Use Projom\Storage\Query\Operators;

Engine::loadDriver([
	'driver' => 'mysql',
	'host' => 'localhost',
	'port' => '3306',
	'dbname' => 'dbname',
	'username' => 'username',
	'password' => 'password'
]);

// Jane creates an account
$userID = DB::query('User')->add(['Username' => 'Jane.doe@example.com',
                                  'Firstname' => 'Jane', 
                                  'Lastname' => 'Doe']);

// Find Janes account
$records = DB::query('User')
             ->filterOn(Operators::EQ, [ 'UserID' => $userID ])
             ->get(['UserID', 'Username']);
var_dump($records);

// John hacks Janes account
$affectedRows = DB::query('User')
                  ->filterOn(Operators::EQ, ['UserID' => '$userID'])
                  ->modify(['Firstname' => 'John', 'Password' => 'password']);

// Remove Janes account because John hacked it
$affectedRows = DB::query('User')->filterOn(Operators::EQ, [ 'UserID' => $userID ])->remove();
````
### Object example
````
use Projom\Storage\Database\Engine;
use Projom\Storage\Database;
Use Projom\Storage\Query\Operators;

Engine::loadDriver([
	'driver' => 'mysql',
	'host' => 'localhost',
	'port' => '3306',
	'dbname' => 'dbname',
	'username' => 'username',
	'password' => 'password'
]);

$database = Database::create();
$database->query('User')->get('*');
...
````
