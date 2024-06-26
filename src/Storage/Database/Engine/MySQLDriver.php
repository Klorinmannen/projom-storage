<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Engine;

use Projom\Storage\Database\Engine\DriverInterface;
use Projom\Storage\Database\Driver\SQL\DeleteQuery;
use Projom\Storage\Database\Driver\SQL\InsertQuery;
use Projom\Storage\Database\Driver\SQL\SelectQuery;
use Projom\Storage\Database\Driver\SQL\UpdateQuery;
use Projom\Storage\Database\Query\QueryObject;

class MySQLDriver implements DriverInterface
{
	private \PDO $pdo;
	private \PDOStatement|null $statement = null;

	public function __construct(\PDO $pdo)
	{
		$this->pdo = $pdo;
	}

	public static function create(\PDO $pdo): MySQLDriver
	{
		return new MySQLDriver($pdo);
	}

	public function select(QueryObject $queryObject): array
	{
		$select = SelectQuery::create($queryObject);

		[$query, $params] = $select->query();

		$this->execute($query, $params);

		return $this->statement->fetchAll();
	}

	public function update(QueryObject $queryObject): int
	{
		$update = UpdateQuery::create($queryObject);

		[$query, $params] = $update->query();

		$this->execute($query, $params);

		return $this->statement->rowCount();
	}

	public function insert(QueryObject $queryObject): int
	{
		$inserted = InsertQuery::create($queryObject);

		[$query, $params] = $inserted->query();

		$this->execute($query, $params);

		return (int) $this->pdo->lastInsertId();
	}

	public function delete(QueryObject $queryObject): int
	{
		$delete = DeleteQuery::create($queryObject);

		[$query, $params] = $delete->query();

		$this->execute($query, $params);

		return (int) $this->statement->rowCount();
	}

	public function execute(string $sql, array|null $params): void
	{
		if (!$this->statement = $this->pdo->prepare($sql))
			throw new \Exception("Failed to prepare statement", 500);

		if (!$this->statement->execute($params))
			throw new \Exception("Failed to execute statement", 500);
	}
}
