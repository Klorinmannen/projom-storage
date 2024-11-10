<?php

declare(strict_types=1);

namespace Projom\Storage\Engine\Driver;

use PDOStatement;

use Projom\Storage\Query\Action;
use Projom\Storage\Engine\Driver\DriverBase;
use Projom\Storage\Engine\Driver\Connection\ConnectionInterface;
use Projom\Storage\Engine\Driver\Connection\PDOConnection;
use Projom\Storage\SQL\QueryObject;
use Projom\Storage\SQL\QueryBuilder;
use Projom\Storage\SQL\Statement\StatementInterface;
use Projom\Storage\SQL\Statement\Delete;
use Projom\Storage\SQL\Statement\Insert;
use Projom\Storage\SQL\Statement\Select;
use Projom\Storage\SQL\Statement\Update;

class MySQL extends DriverBase
{
	private array $connections = [];
	private null|PDOConnection $connection = null;
	private null|PDOStatement $statement = null;

	public function __construct(null|PDOConnection $connection)
	{
		parent::__construct();

		if ($connection === null)
			return;

		$this->connection = $connection;
		$this->connections[$connection->name()] = $connection;
	}

	public static function create(null|PDOConnection $connection = null): MySQL
	{
		return new MySQL($connection);
	}

	public function dispatch(Action $action, mixed $args): mixed
	{
		$this->logger->debug(
			'Method: {method} with {action} and {args}.',
			['action' => $action->name, 'args' => $args, 'method' => __METHOD__]
		);

		return match ($action) {
			Action::CHANGE_CONNECTION => $this->changeConnection($args),
			Action::SELECT => $this->select($args),
			Action::UPDATE => $this->update($args),
			Action::INSERT => $this->insert($args),
			Action::DELETE => $this->delete($args),
			Action::EXECUTE => $this->execute(...$args),
			Action::QUERY => $this->query(...$args),
			Action::START_TRANSACTION => $this->startTransaction(),
			Action::END_TRANSACTION => $this->endTransaction(),
			Action::REVERT_TRANSACTION => $this->revertTransaction(),
			default => throw new \Exception("Action: $action is not supported", 400)
		};
	}

	public function changeConnection(int|string $name): void
	{
		$this->logger->debug(
			'Method: {method} with "{name}".',
			['name' => $name, 'method' => __METHOD__]
		);

		if (!array_key_exists($name, $this->connections))
			throw new \Exception("Connection: '$name' does not exist.", 400);
		$this->connection = $this->connections[$name];
	}

	public function addConnection(ConnectionInterface $connection): void
	{
		$this->logger->debug(
			'Method: {method} with {connection} named "{name}".',
			['connection' => $connection::class, 'name' => $connection->name(), 'method' => __METHOD__]
		);

		if (!$connection instanceof PDOConnection)
			throw new \Exception("Provided connection is not a PDO connection", 400);
		$this->connections[$connection->name()] = $connection;
	}

	private function select(QueryObject $queryObject): null|array|object
	{
		$this->logger->debug(
			'Method: {method} with {queryObject}.',
			['queryObject' => $queryObject, 'method' => __METHOD__]
		);

		$select = Select::create($queryObject);
		$this->executeStatement($select);

		$records = $this->statement->fetchAll();
		if (!$records)
			return null;

		$records = $this->processRecords($records, $queryObject->formatting);

		return $records;
	}

	private function update(QueryObject $queryObject): int
	{
		$this->logger->debug(
			'Method: {method} with {queryObject}.',
			['queryObject' => $queryObject, 'method' => __METHOD__]
		);

		$update = Update::create($queryObject);
		$this->executeStatement($update);
		return $this->statement->rowCount();
	}

	private function insert(QueryObject $queryObject): int
	{
		$this->logger->debug(
			'Method: {method} with {queryObject}.',
			['queryObject' => $queryObject, 'method' => __METHOD__]
		);

		$insert = Insert::create($queryObject);
		$this->executeStatement($insert);
		return (int) $this->connection->lastInsertId();
	}

	private function delete(QueryObject $queryObject): int
	{
		$this->logger->debug(
			'Method: {method} with {queryObject}.',
			['queryObject' => $queryObject, 'method' => __METHOD__]
		);

		$delete = Delete::create($queryObject);
		$this->executeStatement($delete);
		return (int) $this->statement->rowCount();
	}

	private function executeStatement(StatementInterface $statement): void
	{
		$this->logger->debug(
			'Method: {method} with {statement}.',
			['statement' => $statement, 'method' => __METHOD__]
		);

		[$sql, $params] = $statement->statement();
		$this->prepareAndExecute($sql, $params);
	}

	private function prepareAndExecute(string $sql, null|array $params): void
	{
		$this->logger->debug(
			'Method: {method} with "{sql}" and {params}.',
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
		$this->logger->debug(
			'Method {method} with "{sql}" and {params}.',
			['sql' => $sql, 'params' => $params, 'method' => __METHOD__]
		);

		$this->prepareAndExecute($sql, $params);
		return $this->statement->fetchAll();
	}

	private function query(array $collections, null|array $options = null): QueryBuilder
	{
		$this->logger->debug(
			'Method: {method} with {collections} and {options}.',
			['collections' => $collections, 'options' => $options, 'method' => __METHOD__]
		);

		$this->setQueryOptions($options);

		return QueryBuilder::create($this, $collections, $this->logger);
	}

	private function startTransaction(): void
	{
		$this->logger->debug('Starting transaction.');
		$this->connection->beginTransaction();
	}

	private function endTransaction(): void
	{
		$this->logger->debug('Ending transaction.');
		$this->connection->commit();
	}

	private function revertTransaction(): void
	{
		$this->logger->debug('Reverting transaction.');
		$this->connection->rollBack();
	}
}
