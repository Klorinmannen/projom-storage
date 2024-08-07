<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Engine\Driver;

use Projom\Storage\Database\Engine\Driver\Language\QueryInterface;
use Projom\Storage\Database\Engine\DriverInterface;
use Projom\Storage\Database\Engine\Driver\Language\SQL;
use Projom\Storage\Database\Query;
use Projom\Storage\Database\Query\Action;
use Projom\Storage\Database\Query\AggregateFunction;
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

	public function dispatch(Action $action, mixed $args): mixed
	{
		return match ($action) {
			Action::EXECUTE => $this->execute(...$args),
			Action::QUERY => $this->query($args),
			Action::SELECT => $this->select($args),
			Action::UPDATE => $this->update($args),
			Action::INSERT => $this->insert($args),
			Action::DELETE => $this->delete($args),
			Action::START_TRANSACTION => $this->startTransaction(),
			Action::END_TRANSACTION => $this->endTransaction(),
			Action::REVERT_TRANSACTION => $this->revertTransaction(),
			Action::COUNT => $this->count($args),
			default => throw new \Exception("Action: $action is not supported", 400),
		};
	}

	private function execute(string $sql, array|null $params): array
	{
		$this->prepareAndExecute($sql, $params);

		return $this->statement->fetchAll();
	}

	private function count(QueryObject $queryObject): int
	{
		$field = array_pop($queryObject->fields);
		$alias = 'count';
		$functionField = AggregateFunction::COUNT->buildSQL($field, $alias);
		$queryObject->fields = [$functionField];

		$select = SQL::select($queryObject);
		$this->executeQuery($select);

		if (!$result = $this->statement->fetchAll())
			return 0;

		$result = array_pop($result);
		$count = (int) $result[$alias] ?? 0;

		return $count;
	}

	private function query(array $collections): Query
	{
		return Query::create($this, $collections);
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
