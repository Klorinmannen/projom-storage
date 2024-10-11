<?php

declare(strict_types=1);

namespace Projom\Storage\Engine\Driver;

use Projom\Storage\Action;
use Projom\Storage\Engine\DriverBase;
use Projom\Storage\SQL\QueryObject;
use Projom\Storage\SQL\QueryBuilder;
use Projom\Storage\SQL\StatementInterface;
use Projom\Storage\SQL\Statement\Delete;
use Projom\Storage\SQL\Statement\Insert;
use Projom\Storage\SQL\Statement\Select;
use Projom\Storage\SQL\Statement\Update;

class MySQL extends DriverBase
{
	private readonly \PDO $pdo;
	private null|\PDOStatement $statement = null;

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

	private function select(QueryObject $queryObject): null|array
	{
		$select = Select::create($queryObject);

		$this->executeStatement($select);

		$records = $this->statement->fetchAll();
		if (!$records)
			return null;

		if ($formatting = $queryObject->formatting)
			$records = $this->formatRecords($records, ...$formatting);

		if ($this->returnSingleRecord)
			if (count($records) === 1)
				$records = $records[0];

		return $records;
	}

	private function update(QueryObject $queryObject): int
	{
		$update = Update::create($queryObject);

		$this->executeStatement($update);

		return $this->statement->rowCount();
	}

	private function insert(QueryObject $queryObject): int
	{
		$insert = Insert::create($queryObject);

		$this->executeStatement($insert);

		return (int) $this->pdo->lastInsertId();
	}

	private function delete(QueryObject $queryObject): int
	{
		$delete = Delete::create($queryObject);

		$this->executeStatement($delete);

		return (int) $this->statement->rowCount();
	}

	private function executeStatement(StatementInterface $statement): void
	{
		[$sql, $params] = $statement->statement();

		$this->prepareAndExecute($sql, $params);
	}

	private function prepareAndExecute(string $sql, null|array $params): void
	{
		if (!$statement = $this->pdo->prepare($sql))
			throw new \Exception("Failed to prepare statement", 500);

		$this->statement = $statement;
		if (!$this->statement->execute($params))
			throw new \Exception("Failed to execute statement", 500);
	}

	private function execute(string $sql, null|array $params): array
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
