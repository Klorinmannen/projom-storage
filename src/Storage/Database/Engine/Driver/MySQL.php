<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Engine\Driver;

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

		[$query, $params] = $select->query();

		$this->execute($query, $params);

		return $this->statement->fetchAll();
	}

	public function update(QueryObject $queryObject): int
	{
		$update = SQL::update($queryObject);

		[$query, $params] = $update->query();

		$this->execute($query, $params);

		return $this->statement->rowCount();
	}

	public function insert(QueryObject $queryObject): int
	{
		$insert = SQL::insert($queryObject);

		[$query, $params] = $insert->query();

		$this->execute($query, $params);

		return (int) $this->pdo->lastInsertId();
	}

	public function delete(QueryObject $queryObject): int
	{
		$delete = SQL::delete($queryObject);

		[$query, $params] = $delete->query();

		$this->execute($query, $params);

		return (int) $this->statement->rowCount();
	}

	public function execute(string $sql, array|null $params): void
	{
		if (!$statement = $this->pdo->prepare($sql))
			throw new \Exception("Failed to prepare statement", 500);

		$this->statement = $statement;
		if (!$this->statement->execute($params))
			throw new \Exception("Failed to execute statement", 500);
	}

	public function query(string $sql, array|null $params): array
	{
		$this->execute($sql, $params);

		return $this->statement->fetchAll();
	}
}
