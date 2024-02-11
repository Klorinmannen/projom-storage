<?php

declare(strict_types=1);

namespace Projom\Storage;

use Projom\Storage\Query\Driver\PDO;

enum Driver {
	case PDO;
}

class Query
{
	public static function simple(string $query): mixed
	{
		return static::exec($query, null, Driver::PDO);
	}

	public static function pdo(string $query, ?array $params = null): mixed
	{
		return static::exec($query, $params, Driver::PDO);
	}

	public static function exec(string $query, ?array $params = null, Driver $driver = Driver::PDO): mixed
	{
		switch ($driver) {
			case Driver::PDO:
				return PDO::invoke($query, $params);

			default:
				throw new \Exception('Invalid driver', 500);
		}
	}
}