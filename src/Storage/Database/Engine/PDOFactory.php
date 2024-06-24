<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Engine\Source;

use Projom\Storage\Database\Engine\Config;
use Projom\Storage\Database\Engine\DSN;

class PDOFactory
{
	const DEFAULT_PDO_OPTIONS = [
		\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
		\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
	];

	public static function MySQL(Config $config): \PDO 
	{
		$dsn = DSN::MySQL($config);
		$options = static::parseAttributes($config->options);
		return new \PDO(
			$dsn,
			$username ?? null,
			$password ?? null,
			$options + static::DEFAULT_PDO_OPTIONS
		);
	}

	public static function parseAttributes(array $attributes): array
	{
		$pdoAttributes = [];
		foreach ($attributes as $key => $value)
			$pdoAttributes[constant("PDO::ATTR_$key")] = constant("PDO::$value");

		return $pdoAttributes;
	}
}
