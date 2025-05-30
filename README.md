# Projom storage module
[![PHP version support][php-version-badge]][php]
[![PHPUnit][phpunit-ci-badge]][phpunit-action]

[php-version-badge]: https://img.shields.io/badge/php-%5E8.2-7A86B8
[php]: https://www.php.net/supported-versions.php
[phpunit-action]: https://github.com/Klorinmannen/projom-storage/actions
[phpunit-ci-badge]: https://github.com/Klorinmannen/projom-storage/workflows/PHPUnit/badge.svg

### Project goals
* Accessing databases and data/configuration files with a simple interface.
* Should be easy to understand and use.
* Lightweight.
* Support for MySQL/MariaDB and files formatted as csv, json and yaml.

###  [Composer](https://getcomposer.org/doc/00-intro.md)
````
composer require klorinmannen/projom-storage
````

## Docs & coverage
Visit the repository [wiki](https://github.com/Klorinmannen/projom-storage/wiki) pages or the api [documentation](https://projom.se/docs/projom-storage-phpdoc/).
<br>Unit test [coverage](https://projom.se/docs/projom-storage-phpunit/).

## Usage
````
use Projom\Storage\Engine;
use Projom\Storage\MySQL\Query;

$config = [ 
   'driver' => 'mysql',
   'connections' => [
      [
         'name' => 'connection-name',
         'username' => 'username',
         'password' => 'password',
         'host' => 'localhost',
         'port' => '3306',
         'database' => 'database-name'
      ]
   ]
];
$engine = Engine::create($config);
$query = Query::create($engine);

// Select all users
$users = $query->build('User')->select();
````

### Facades

#### Engine
````
use Projom\Storage\Engine as StorageEngine;
use Projom\Storage\Facade\Engine as FacadeEngine;
use Projom\Storage\Facade\MySQL\Query;

// Bootstrap: Create the engine object and set the engine facade
$config = [ 
   'driver' => 'mysql',
   'connections' => [
      [
         'name' => 'connection-name',
         'username' => 'username',
         'password' => 'password',
         'host' => 'localhost',
         'port' => '3306',
         'database' => 'database-name'
      ]
   ]
];
$storageEngine = StorageEngine::create($config);
FacadeEngine::setInstance($storageEngine);
````
#### Query
````
use Projom\Storage\Facade\MySQL\Query;

$users = Query::build('User')->select();
````
#### Repository 
````
use Projom\Storage\Facade\MySQL\Repository;

class UserRepository 
{
   use Repository;
}

$user = UserRepository::find($userID = 3);
````
