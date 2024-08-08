<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Engine\Driver;

use Projom\Storage\Database\Engine\Driver\Language\QueryInterface;
use Projom\Storage\Database\Engine\DriverInterface;
use Projom\Storage\Database\Engine\Driver\Language\SQL;
use Projom\Storage\Database\Query;
use Projom\Storage\Database\Query\Action;
use Projom\Storage\Database\Query\QueryObject;
use Projom\Storage\Database\Util;

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

	public function dispatch(Action $action, mixed $args): mixed
	{
		return match ($action) {
			Action::SELECT => $this->select($args),
			Action::UPDATE => $this->update($args),
			Action::INSERT => $this->insert($args),
			Action::DELETE => $this->delete($args),
			Action::EXECUTE => $this->execute(...$args),
			Action::QUERY => $this->query($args),
			Action::COUNT => $this->count($args),
			Action::SUM => $this->sum($args),
			Action::AVG => $this->avg($args),
			Action::MAX => $this->max($args),
			Action::MIN => $this->min($args),
			Action::START_TRANSACTION => $this->startTransaction(),
			Action::END_TRANSACTION => $this->endTransaction(),
			Action::REVERT_TRANSACTION => $this->revertTransaction(),
			default => throw new \Exception("Action: $action is not supported", 400),
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

	private function count(QueryObject $queryObject): null|int
	{
		$select = SQL::select($queryObject);

		$this->executeQuery($select);

		if (!$result = $this->statement->fetch())
			return null;

		$count = array_pop($result);

		return (int) $count;
	}

	private function sum(QueryObject $queryObject): null|int|float
	{
		$select = SQL::select($queryObject);

		$this->executeQuery($select);

		if (!$result = $this->statement->fetch())
			return null;

		$sum = (string) array_pop($result);

		if (Util::is_int($sum))
			return (int) $sum;

		return (float) $sum;
	}

	private function avg(QueryObject $queryObject): null|float
	{
		$select = SQL::select($queryObject);

		$this->executeQuery($select);

		if (!$result = $this->statement->fetch())
			return null;

		$avg = array_pop($result);

		return (float) $avg;
	}

	private function max(QueryObject $queryObject): null|string
	{
		$select = SQL::select($queryObject);

		$this->executeQuery($select);

		if (!$result = $this->statement->fetch())
			return null;

		$max = array_pop($result);

		return (string) $max;
	}

	private function min(QueryObject $queryObject): null|string
	{
		$select = SQL::select($queryObject);

		$this->executeQuery($select);

		if (!$result = $this->statement->fetch())
			return null;

		$max = array_pop($result);

		return (string) $max;
	}

	private function query(array $collections): Query
	{
		return Query::create($this, $collections);
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
