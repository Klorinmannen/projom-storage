<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Engine\Driver;

use Projom\Storage\Database\Action;
use Projom\Storage\Database\Language\SQL\QueryInterface;
use Projom\Storage\Database\Engine\DriverInterface;
use Projom\Storage\Database\Language\SQL;
use Projom\Storage\Database\MySQL\QueryBuilder;
use Projom\Storage\Database\MySQL\QueryObject;

class MySQL implements DriverInterface
{
	private readonly \PDO $pdo;
	private \PDOStatement|null $statement = null;

	public function __construct(\PDO $pdo)
	{
		$this->pdo = $pdo;
	}

	public static function create(\PDO $pdo): MySQL
	{
		return new MySQL($pdo);
	}

	public function dispatch(Action $action, mixed $args): mixed
	{
		return match ($action) {
			Action::SELECT => $this->select($args),
			Action::UPDATE => $this->update($args),
			Action::INSERT => $this->insert($args),
			Action::DELETE => $this->delete($args),
			Action::EXECUTE => $this->execute(...$args),
			Action::QUERY => $this->query($args),
			Action::START_TRANSACTION => $this->startTransaction(),
			Action::END_TRANSACTION => $this->endTransaction(),
			Action::REVERT_TRANSACTION => $this->revertTransaction(),
			default => throw new \Exception("Action: $action is not supported", 400)
		};
	}

	private function select(QueryObject $queryObject): array
	{
		$select = SQL::select($queryObject);

		$this->executeQuery($select);

		return $this->statement->fetchAll();
	}

	private function update(QueryObject $queryObject): int
	{
		$update = SQL::update($queryObject);

		$this->executeQuery($update);

		return $this->statement->rowCount();
	}

	private function insert(QueryObject $queryObject): int
	{
		$insert = SQL::insert($queryObject);

		$this->executeQuery($insert);

		return (int) $this->pdo->lastInsertId();
	}

	private function delete(QueryObject $queryObject): int
	{
		$delete = SQL::delete($queryObject);

		$this->executeQuery($delete);

		return (int) $this->statement->rowCount();
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

	private function execute(string $sql, array|null $params): array
	{
		$this->prepareAndExecute($sql, $params);

		return $this->statement->fetchAll();
	}

	private function query(array $collections): QueryBuilder
	{
		return QueryBuilder::create($this, $collections);
	}

	private function startTransaction(): void
	{
		$this->pdo->beginTransaction();
	}

	private function endTransaction(): void
	{
		$this->pdo->commit();
	}

	private function revertTransaction(): void
	{
		$this->pdo->rollBack();
	}
}
