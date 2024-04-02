<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Driver;

use Projom\Storage\Database\DriverInterface;
use Projom\Storage\Database\Query;
use Projom\Storage\Database\Query\Collection as QCollection;
use Projom\Storage\Database\Query\Field as QField;
use Projom\Storage\Database\Query\Filter as QFilter;
use Projom\Storage\Database\Driver\MySQL\Statement;
use Projom\Storage\Database\Driver\MySQL\Column;
use Projom\Storage\Database\Driver\MySQL\Table;
use Projom\Storage\Database\Driver\MySQL\Filter;
use Projom\Storage\Database\Drivers;
use Projom\Storage\Database\PDO\Source;
use Projom\Storage\Database\SourceInterface;

class MySQL implements DriverInterface
{
	private SourceInterface $source;
	protected Drivers $driver = Drivers::MySQL;

	public function __construct(Source $source)
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

	public function select(QCollection $collection, QField $field, QFilter $QFilter): mixed
	{
		$table = Table::create($collection->get());
		$column = Column::create($field->get());
		$filter = Filter::create($QFilter->get());

		$statement = Statement::create($table, $column, $filter);
		[ $query, $params ] = $statement->select();

		return $this->source->execute($query, $params);
	}

	public function Query(string $table): Query
	{
		return new Query($this, $table);
	}
	
	public function execute(string $sql, ?array $params): mixed 
	{ 
		return $this->source->execute($sql, $params);
	}
}