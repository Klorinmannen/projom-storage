<?php

declare(strict_types=1);

namespace Projom\Storage\Database\PDO;

use PDO;
use Exception;

use Projom\Storage\Database\PDO\DSN;

trait Source
{
	use DSN;

	protected static PDO|null $PDO = null;

	const DEFAULT_PDO_OPTIONS = [
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
	];

	protected function connect(array $config, array $options = [])
	{
		if (static::$PDO !== null)
			return;
		
		static::$PDO = new PDO(
			static::DSN($config),
			$config['username'] ?? null,
			$config['password'] ?? null,
			static::DEFAULT_PDO_OPTIONS + $options
		);
	}

	public function execute(string $query, ?array $params = null): array
	{
		if (static::$PDO === null)
			throw new Exception('PDO not initialized', 400);

		if (!$statement = static::$PDO->prepare($query))
			throw new Exception('Failed to prepare PDO query', 500);
		
		if (!$statement->execute($params))
			throw new Exception('Failed to execute PDO query', 500);
			
		$result = $statement->fetchAll();
		if ($result === false)
			throw new Exception('Failed to fetch PDO query result', 500);

		return $result;
	}
}
