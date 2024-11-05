<?php

declare(strict_types=1);

namespace Projom\Storage\Engine\Driver;

use PDOStatement;

use Projom\Storage\Action;
use Projom\Storage\Engine\DriverBase;
use Projom\Storage\Engine\Driver\ConnectionInterface;
use Projom\Storage\SQL\QueryObject;
use Projom\Storage\SQL\QueryBuilder;
use Projom\Storage\SQL\StatementInterface;
use Projom\Storage\SQL\Statement\Delete;
use Projom\Storage\SQL\Statement\Insert;
use Projom\Storage\SQL\Statement\Select;
use Projom\Storage\SQL\Statement\Update;
use Psr\Log\LogLevel;

class MySQL extends DriverBase
{
	private array $connections = [];
	private null|PDOConnection $connection;
	private null|PDOStatement $statement = null;

	public function __construct(null|PDOConnection $connection, string $name)
	{
		if ($connection === null)
			return;

		$this->setConnection($connection, $name);
		$this->changeConnection($name);
	}

	public static function create(null|PDOConnection $connection = null, string $name = 'default'): MySQL
	{
		return new MySQL($connection, $name);
	}

	public function dispatch(Action $action, mixed $args): mixed
	{
		$this->log(
			LogLevel::DEBUG,
			'Method: {method} with action: {action} and args: {args}.',
			['action' => $action->name, 'args' => $args, 'method' => __METHOD__]
		);

		try {
			return match ($action) {
				Action::CHANGE_CONNECTION => $this->changeConnection($args),
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
		} catch (\Exception $e) {
			$this->log(LogLevel::ERROR, 'Error dispatching action. {exception}', ['exception' => $e]);
			throw $e;
		}
	}

	public function changeConnection(int|string $name): void
	{
		$this->log(
			LogLevel::DEBUG,
			'Method: {method} with "{name}".',
			['name' => $name, 'method' => __METHOD__]
		);

		if (!array_key_exists($name, $this->connections))
			throw new \Exception("Connection: '$name' does not exist.", 400);
		$this->connection = $this->connections[$name];
	}

	public function setConnection(ConnectionInterface $connection, int|string $name): void
	{
		$this->log(
			LogLevel::DEBUG,
			'Method: {method} with {connection} named "{name}".',
			['connection' => $connection::class, 'name' => $name, 'method' => __METHOD__]
		);

		if (!$connection instanceof PDOConnection)
			throw new \Exception("Provided connection is not a PDO connection", 400);
		$this->connections[$name] = $connection;
	}

	private function select(QueryObject $queryObject): null|array|object
	{
		$this->log(
			LogLevel::DEBUG,
			'Method: {method} with {queryObject}.',
			['queryObject' => $queryObject, 'method' => __METHOD__]
		);

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
		$this->log(
			LogLevel::DEBUG,
			'Method: {method} with {queryObject}.',
			['queryObject' => $queryObject, 'method' => __METHOD__]
		);

		$update = Update::create($queryObject);
		$this->executeStatement($update);
		return $this->statement->rowCount();
	}

	private function insert(QueryObject $queryObject): int
	{
		$this->log(
			LogLevel::DEBUG,
			'Method: {method} with {queryObject}.',
			['queryObject' => $queryObject, 'method' => __METHOD__]
		);

		$insert = Insert::create($queryObject);
		$this->executeStatement($insert);
		return (int) $this->connection->lastInsertId();
	}

	private function delete(QueryObject $queryObject): int
	{
		$this->log(
			LogLevel::DEBUG,
			'Method: {method} with {queryObject}.',
			['queryObject' => $queryObject, 'method' => __METHOD__]
		);

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
		$this->log(
			LogLevel::DEBUG,
			'Method: {method} with sql: {sql} and params: {params}.',
			['sql' => $sql, 'params' => $params, 'method' => __METHOD__]
		);

		if (!$statement = $this->connection->prepare($sql))
			throw new \Exception('Failed to prepare statement.', 500);

		$this->statement = $statement;
		if (!$this->statement->execute($params))
			throw new \Exception('Failed to execute statement.', 500);
	}

	private function execute(string $sql, null|array $params): array
	{
		$this->log(
			LogLevel::DEBUG,
			'Method {method} with sql: {sql} and params: {params}.',
			['sql' => $sql, 'params' => $params, 'method' => __METHOD__]
		);

		$this->prepareAndExecute($sql, $params);
		return $this->statement->fetchAll();
	}

	private function query(array $collections): QueryBuilder
	{
		$this->log(
			LogLevel::DEBUG,
			'Method: {method} with collections {collections}.',
			['collections' => $collections, 'method' => __METHOD__]
		);

		return QueryBuilder::create($this, $collections, $this->logger);
	}

	private function startTransaction(): void
	{
		$this->log(LogLevel::INFO, 'Starting transaction.');
		$this->connection->beginTransaction();
	}

	private function endTransaction(): void
	{
		$this->log(LogLevel::INFO, 'Ending transaction.');
		$this->connection->commit();
	}

	private function revertTransaction(): void
	{
		$this->log(LogLevel::INFO, 'Reverting transaction.');
		$this->connection->rollBack();
	}
}
