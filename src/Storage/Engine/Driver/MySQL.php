<?php

declare(strict_types=1);

namespace Projom\Storage\Engine\Driver;

use PDOStatement;

use Projom\Storage\Query\Action;
use Projom\Storage\Engine\Driver\DriverBase;
use Projom\Storage\Engine\Driver\Connection\ConnectionInterface;
use Projom\Storage\Engine\Driver\Connection\PDOConnection;
use Projom\Storage\SQL\Statement;
use Projom\Storage\SQL\Statement\Builder;
use Projom\Storage\SQL\Statement\DTO;
use Projom\Storage\SQL\Statement\StatementInterface;

class MySQL extends DriverBase
{
	private PDOConnection $connection;
	private Statement $SQLStatement;
	private array $connections = [];
	private null|PDOStatement $PDOSstatement = null;

	public function __construct(PDOConnection $connection, Statement $statement)
	{
		parent::__construct();
		$this->connection = $connection;
		$this->connections[$connection->name()] = $connection;
		$this->SQLStatement = $statement;
	}

	public static function create(PDOConnection $PDOConnection, Statement $statement): MySQL
	{
		return new MySQL($PDOConnection, $statement);
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

	private function select(DTO $dto): null|array|object
	{
		$this->logger->debug(
			'Method: {method} with {queryObject}.',
			['queryObject' => $dto, 'method' => __METHOD__]
		);

		$selectStatement = $this->SQLStatement->select($dto);
		$this->executeStatement($selectStatement);

		$records = $this->PDOSstatement->fetchAll();
		if (!$records)
			return null;

		$records = $this->processRecords($records, $dto->formatting, $dto->options);

		return $records;
	}

	private function update(DTO $dto): int
	{
		$this->logger->debug(
			'Method: {method} with {queryObject}.',
			['queryObject' => $dto, 'method' => __METHOD__]
		);

		$updateStatement = $this->SQLStatement->update($dto);
		$this->executeStatement($updateStatement);
		return $this->PDOSstatement->rowCount();
	}

	private function insert(DTO $dto): int
	{
		$this->logger->debug(
			'Method: {method} with {queryObject}.',
			['queryObject' => $dto, 'method' => __METHOD__]
		);

		$insertStatement = $this->SQLStatement->insert($dto);
		$this->executeStatement($insertStatement);
		return (int) $this->connection->lastInsertId();
	}

	private function delete(DTO $dto): int
	{
		$this->logger->debug(
			'Method: {method} with {queryObject}.',
			['queryObject' => $dto, 'method' => __METHOD__]
		);

		$deleteStatement = $this->SQLStatement->delete($dto);
		$this->executeStatement($deleteStatement);
		return (int) $this->PDOSstatement->rowCount();
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

		$this->PDOSstatement = $statement;
		if (!$this->PDOSstatement->execute($params))
			throw new \Exception('Failed to execute statement.', 500);
	}

	private function execute(string $sql, null|array $params = null): array
	{
		$this->logger->debug(
			'Method {method} with "{sql}" and {params}.',
			['sql' => $sql, 'params' => $params, 'method' => __METHOD__]
		);

		$this->prepareAndExecute($sql, $params);
		return $this->PDOSstatement->fetchAll();
	}

	private function query(array $collections, array $options = []): Builder
	{
		$this->logger->debug(
			'Method: {method} with {collections} and {options}.',
			['collections' => $collections, 'options' => $options, 'method' => __METHOD__]
		);

		return Builder::create($this, $collections, $options, $this->logger);
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
