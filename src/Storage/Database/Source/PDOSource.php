<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Source;

use PDO;
use Exception;
use PDOStatement;
use Projom\Storage\Database\Driver\QueryInterface;
use Projom\Storage\Database\SourceInterface;

class PDOSource implements SourceInterface
{
	private PDO|null $pdo = null;
	private PDOStatement|false|null $statement = null;

	public function __construct(PDO $pdo)
	{
		$this->pdo = $pdo;
	}

	public static function create(PDO $pdo): PDOSource
	{
		return new PDOSource($pdo);
	}

	public function run(QueryInterface $query): void
	{
		[$query, $params] = $query->query();
		$this->execute($query, $params);
	}

	public function execute(string $sql, array|null $params = null): void
	{
		if (!$this->statement = $this->pdo->prepare($sql))
			throw new Exception('Failed to prepare PDO query', 500);

		if (!$this->statement->execute($params))
			throw new Exception('Failed to execute PDO statement', 500);		
	}

	public function get(): PDO
	{
		return $this->pdo;
	}

	public function fetchResult(): array
	{
		return $this->statement->fetchAll();
	}

	public function rowsAffected(): int
	{
		return $this->statement->rowCount();
	}

	public function insertedID(): int
	{
		return (int) $this->pdo->lastInsertId();
	}

	public function startTransaction(): bool
	{
		return $this->pdo->beginTransaction();
	}

	public function endTransaction(): bool
	{
		return $this->pdo->commit();
	}

	public function cancelTransaction(): bool
	{
		return $this->pdo->rollBack();
	}
}
