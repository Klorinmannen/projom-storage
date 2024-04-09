<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Source;

use PDO;
use Exception;
use PDOStatement;
use Projom\Storage\Database\SourceInterface;

class PDOSource implements SourceInterface
{
	private PDO|null $pdo = null;
	private PDOStatement|null $statement = null;

	public function __construct(PDO $pdo)
	{
		$this->pdo = $pdo;
	}

	public static function create(PDO $pdo): PDOSource
	{
		return new PDOSource($pdo);
	}

	public function get(): PDO
	{
		return $this->pdo;
	}

	public function execute(string $query, array|null $params = null): array
	{
		if (!$this->statement = $this->pdo->prepare($query))
			throw new Exception('Failed to prepare PDO query', 500);

		if (!$this->statement->execute($params))
			throw new Exception('Failed to execute PDO query', 500);

		return $this->statement->fetchAll();
	}

	public function rowsAffected(): int
	{
		return $this->statement->rowCount();
	}

	public function lastInsertedID(): int
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
