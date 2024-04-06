<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Source;

use Projom\Storage\Database\Source\DSN;
use Projom\Storage\Database\Source\PDOSource;

use PDO;

class Factory
{
	use DSN;

	const DEFAULT_PDO_OPTIONS = [
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
	];

	public static function createPDO(array $config, array $options = []): PDOSource
	{
		$pdo = new PDO(
			static::DSN($config),
			$config['username'] ?? null,
			$config['password'] ?? null,
			$options + static::DEFAULT_PDO_OPTIONS
		);

		return PDOSource::create($pdo);
	}
}