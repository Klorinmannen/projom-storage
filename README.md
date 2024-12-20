# Projom storage module
[![PHP version support][php-version-badge]][php]
[![PHPUnit][phpunit-ci-badge]][phpunit-action]

[php-version-badge]: https://img.shields.io/badge/php-%5E8.1-7A86B8
[php]: https://www.php.net/supported-versions.php
[phpunit-action]: https://github.com/Klorinmannen/projom-storage/actions
[phpunit-ci-badge]: https://github.com/Klorinmannen/projom-storage/workflows/PHPUnit/badge.svg

### Project goals
* Accessing databases with a simple interface
* Should be easy to understand and use
* Lightweight
* Support for MySQL/MariaDB, SQLite and JSON

###  [Composer](https://getcomposer.org/doc/00-intro.md)
````
composer require klorinmannen/projom-storage
````

### Docs & coverage
Visit the repository [wiki](https://github.com/Klorinmannen/projom-storage/wiki) pages or the api [documentation](https://projom.se/docs/projom-storage-phpdoc/).
<br>Unit test [coverage](https://projom.se/docs/projom-storage-phpunit/).

### Usage
````
use Projom\Storage\Engine;
use Projom\Storage\Query\MySQLQuery;

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

Engine::start();
Engine::loadDriver($config);

// Select users
$users = MySQLQuery::query('User')->select();
````