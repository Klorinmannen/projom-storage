<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Engine;

use Projom\Storage\Database\Driver\MySQL;
use Projom\Storage\Database\Engine\Config;
use Projom\Storage\Database\Engine\Source\PDOFactory;

class DriverFactory
{
	public static function MySQL(Config $config): object
	{
		$pdo = PDOFactory::MySQL($config);
		return MySQL::create($pdo);
	}
}
