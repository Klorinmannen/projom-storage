# Projom storage module
[![PHPUnit](https://github.com/Klorinmannen/projom-storage/workflows/PHPUnit/badge.svg)](https://github.com/Klorinmannen/projom-storage/actions)

### The goals of this project are
* Accessing databases with a simple interface
* Should be easy to understand, *no black magic*
* Lightweight, *no dependencies*
* Support for MySQL, MariaDB, SQLite and JSON

### Features so far
* MySQL with PDO
* CRUD operations
* Basic filter stacking with AND/OR

## How to install?
####  [Use composer](https://getcomposer.org/doc/00-intro.md)

````
composer require Klorinmannen/projom-storage
````
#### [Clone the repository](https://git-scm.com/docs/git-clone)
````
git clone https://github.com/Klorinmannen/projom-storage.git
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
			 ->filterOn(Opertators::EQ, [ 'UserID' => $userID ])
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
