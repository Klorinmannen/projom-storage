<?php

declare(strict_types=1);

namespace Projom\Storage\Source;

use Projom\Storage\Source\DSN;

class PDO
{
	private static \PDO|null $PDO = null;

	const DEFAULT_PDO_OPTIONS = [
		\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
		\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
	];

	public static function get(): \PDO
	{
		return static::$PDO;
	}

	public static function set(array $config): void
	{
		static::$PDO = static::create($config);
	}

	public static function create(array $config): \PDO	
	{
		$dsn = DSN::createString($config);
		$options = static::DEFAULT_PDO_OPTIONS;
		$pdo = new \PDO(
			$dsn,
			$config['username'],
			$config['password'],
			$options
		);

		return $pdo;
	}
}
