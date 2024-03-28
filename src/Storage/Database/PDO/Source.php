<?php

declare(strict_types=1);

namespace Projom\Storage\Database\PDO;

use PDO;
use Exception;

use Projom\Storage\Database\PDO\DSN;

trait Source
{
	use DSN;

	private PDO|null $pdo = null;

	const DEFAULT_PDO_OPTIONS = [
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
	];

	protected function connect(array $config, array $options = [])
	{
		if ($this->pdo !== null)
			return;
		
		static::$pdo = new PDO(
			static::DSN($config),
			$config['username'] ?? null,
			$config['password'] ?? null,
			$options + static::DEFAULT_PDO_OPTIONS
		);
	}

	public function quote(string $value): string
	{
		if ($this->pdo === null)
			throw new Exception('PDO not initialized', 400);

		return $this->pdo->quote($value);
	}

	public function execute(string $query, ?array $params = null): array
	{
		if ($this->pdo === null)
			throw new Exception('PDO not initialized', 400);

		if (!$statement = $this->pdo->prepare($query))
			throw new Exception('Failed to prepare PDO query', 500);
		
		if (!$statement->execute($params))
			throw new Exception('Failed to execute PDO query', 500);
			
		$result = $statement->fetchAll();
		if ($result === false)
			throw new Exception('Failed to fetch PDO query result', 500);

		return $result;
	}
}
