<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Driver;

use Projom\Storage\Database\Driver\DriverInterface;
use Projom\Storage\Database\Driver\Driver;
use Projom\Storage\Database\Driver\SQL\Delete;
use Projom\Storage\Database\Driver\SQL\Insert;
use Projom\Storage\Database\Driver\SQL\Select;
use Projom\Storage\Database\Driver\SQL\Update;
use Projom\Storage\Database\Query;
use Projom\Storage\Database\Query\QueryObject;
use Projom\Storage\Database\SourceInterface;
use Projom\Storage\Database\Source\PDOSource;

class MySQL implements DriverInterface
{
	private PDOSource $source;
	protected Driver $driver = Driver::MySQL;

	public function __construct(PDOSource $source)
	{
		$this->source = $source;
	}

	public static function create(SourceInterface $source): MySQL
	{
		return new MySQL($source);
	}

	public function type(): Driver
	{
		return $this->driver;
	}

	public function select(QueryObject $queryObject): array
	{
		$select = Select::create($queryObject);

		$this->source->run($select);

		return $this->source->fetchResult();
	}

	public function update(QueryObject $queryObject): int
	{
		$update = Update::create($queryObject);

		$this->source->run($update);

		return $this->source->rowsAffected();
	}

	public function insert(QueryObject $queryObject): int
	{
		$inserted = Insert::create($queryObject);

		$this->source->run($inserted);

		return $this->source->insertedID();
	}

	public function delete(QueryObject $queryObject): int
	{
		$delete = Delete::create($queryObject);

		$this->source->run($delete);

		return $this->source->rowsAffected();
	}

	public function Query(string ...$tables): Query
	{
		return Query::create($this, $tables);
	}

	public function execute(string $sql, array|null $params): array
	{
		$this->source->execute($sql, $params);
		return $this->source->fetchResult();
	}
}
