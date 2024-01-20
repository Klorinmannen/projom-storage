<?php

declare(strict_types=1);

namespace Projom\Storage;

use PDO;

use Projom\Storage\Connection;
use Projom\Storage\Dsn;

class ConnectionFactory
{
	const DEFAULT_PDO_OPTIONS = [
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
	];

	public static function create(array $config): Connection
	{
		$dsn = Dsn::createString($config);
		$options = static::DEFAULT_PDO_OPTIONS;
		$pdo = static::createPDO(
			$dsn,
			$config['username'],
			$config['password'],
			$options
		);

		return new Connection($pdo);
	}

	public static function createPDO(
		string $dsn,
		string $username,
		string $password,
		array $options
	): PDO {
		return new PDO(
			$dsn,
			$username,
			$password,
			$options
		);
	}
}
