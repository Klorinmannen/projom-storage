<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Driver;

use Projom\Storage\Database\Driver\DriverInterface;
use Projom\Storage\Database\Driver\SQL\Delete;
use Projom\Storage\Database\Driver\SQL\Insert;
use Projom\Storage\Database\Driver\SQL\Select;
use Projom\Storage\Database\Driver\SQL\Update;
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
		$select = Select::create($queryObject);

		[$query, $params] = $select->query();

		$this->execute($query, $params);

		return $this->statement->fetchAll();
	}

	public function update(QueryObject $queryObject): int
	{
		$update = Update::create($queryObject);

		[$query, $params] = $update->query();

		$this->execute($query, $params);

		return $this->statement->rowCount();
	}

	public function insert(QueryObject $queryObject): int
	{
		$inserted = Insert::create($queryObject);

		[$query, $params] = $inserted->query();

		$this->execute($query, $params);

		return (int) $this->pdo->lastInsertId();
	}

	public function delete(QueryObject $queryObject): int
	{
		$delete = Delete::create($queryObject);

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
