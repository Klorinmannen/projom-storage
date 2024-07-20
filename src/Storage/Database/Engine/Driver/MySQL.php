<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Engine\Driver;

use Projom\Storage\Database\Engine\Driver\Language\QueryInterface;
use Projom\Storage\Database\Engine\DriverInterface;
use Projom\Storage\Database\Engine\Driver\Language\SQL;
use Projom\Storage\Database\Query\QueryObject;

class MySQL implements DriverInterface
{
	private \PDO $pdo;
	private \PDOStatement|null $statement = null;

	public function __construct(\PDO $pdo)
	{
		$this->pdo = $pdo;
	}

	public static function create(\PDO $pdo): MySQL
	{
		return new MySQL($pdo);
	}

	public function select(QueryObject $queryObject): array
	{
		$select = SQL::select($queryObject);

		$this->executeQuery($select);

		return $this->statement->fetchAll();
	}

	public function update(QueryObject $queryObject): int
	{
		$update = SQL::update($queryObject);

		$this->executeQuery($update);

		return $this->statement->rowCount();
	}

	public function insert(QueryObject $queryObject): int
	{
		$insert = SQL::insert($queryObject);

		$this->executeQuery($insert);

		return (int) $this->pdo->lastInsertId();
	}

	public function delete(QueryObject $queryObject): int
	{
		$delete = SQL::delete($queryObject);

		$this->executeQuery($delete);

		return (int) $this->statement->rowCount();
	}

	public function execute(string $sql, array|null $params): array
	{
		$this->prepareAndExecute($sql, $params);

		return $this->statement->fetchAll();
	}

	private function executeQuery(QueryInterface $query): void
	{
		[$query, $params] = $query->query();

		$this->prepareAndExecute($query, $params);
	}

	private function prepareAndExecute(string $sql, array|null $params): void
	{
		if (!$statement = $this->pdo->prepare($sql))
			throw new \Exception("Failed to prepare statement", 500);

		$this->statement = $statement;
		if (!$this->statement->execute($params))
			throw new \Exception("Failed to execute statement", 500);
	}

	public function startTransaction(): void
	{
		$this->pdo->beginTransaction();
	}

	public function endTransaction(): void
	{
		$this->pdo->commit();
	}

	public function revertTransaction(): void
	{
		$this->pdo->rollBack();
	}
}
