<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Source;

use PDO;
use Exception;

use Projom\Storage\Database\SourceInterface;

class PDOSource implements SourceInterface
{
	private PDO|null $pdo = null;

	public function __construct(PDO $pdo)
	{
		$this->pdo = $pdo;
	}

	public static function create(PDO $pdo): PDOSource
	{
		return new PDOSource($pdo);
	}

	public function get(): object
	{
		return $this->pdo;
	}

	public function execute(string $query, array|null $params = null): array
	{
		if (!$statement = $this->pdo->prepare($query))
			throw new Exception('Failed to prepare PDO query', 500);

		if (!$statement->execute($params))
			throw new Exception('Failed to execute PDO query', 500);

		return $statement->fetchAll();
	}
}
