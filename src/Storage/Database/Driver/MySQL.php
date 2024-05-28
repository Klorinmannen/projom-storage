<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Driver;

use Projom\Storage\Database\DriverInterface;
use Projom\Storage\Database\Drivers;
use Projom\Storage\Database\Driver\MySQL\Delete;
use Projom\Storage\Database\Driver\MySQL\Insert;
use Projom\Storage\Database\Driver\MySQL\Select;
use Projom\Storage\Database\Driver\MySQL\Update;
use Projom\Storage\Database\Query;
use Projom\Storage\Database\Query\Delete as QueryDelete;
use Projom\Storage\Database\Query\Insert as QueryInsert;
use Projom\Storage\Database\Query\Select as QuerySelect;
use Projom\Storage\Database\Query\Update as QueryUpdate;
use Projom\Storage\Database\SourceInterface;
use Projom\Storage\Database\Source\PDOSource;

class MySQL implements DriverInterface
{
	private PDOSource $source;
	protected Drivers $driver = Drivers::MySQL;

	public function __construct(PDOSource $source)
	{
		$this->source = $source;
	}

	public static function create(SourceInterface $source): MySQL
	{
		return new MySQL($source);
	}

	public function type(): Drivers
	{
		return $this->driver;
	}

	public function select(QuerySelect $select): array
	{
		$select = Select::create($select);

		return $this->source->run($select);
	}

	public function update(QueryUpdate $update): int
	{
		$update = Update::create($update);

		$this->source->run($update);

		return $this->source->rowsAffected();
	}

	public function insert(QueryInsert $insert): int
	{
		$inserted = Insert::create($insert);

		$this->source->run($inserted);

		return $this->source->lastInsertedID();
	}

	public function delete(QueryDelete $delete): int
	{
		$delete = Delete::create($delete);

		$this->source->run($delete);

		return $this->source->rowsAffected();
	}

	public function Query(string ...$tables): Query
	{
		return Query::create($this, $tables);
	}

	public function execute(string $sql, array|null $params): array
	{
		return $this->source->execute($sql, $params);
	}
}
