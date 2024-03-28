<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Driver;

use Projom\Storage\Database\DriverInterface;
use Projom\Storage\Database\Query;
use Projom\Storage\Database\PDO\Source;
use Projom\Storage\Database\Query\Collection;
use Projom\Storage\Database\Query\Field;
use Projom\Storage\Database\Query\Filter as QFilter;
use Projom\Storage\Database\Driver\MySQL\Statement;
use Projom\Storage\Database\Driver\MySQL\Column;
use Projom\Storage\Database\Driver\MySQL\Table;
use Projom\Storage\Database\Driver\MySQL\Filter;

class MySQL implements DriverInterface
{
	use Source;

	public function __construct(array $config)
	{
		$this->connect($config);
	}

	public function select(Collection $collection, Field $field, QFilter $QFilter): mixed
	{
		$table = Table::create($collection->get());
		$column = Column::create($field->get());
		$filter = Filter::create($QFilter->get());

		$statement = Statement::create($table, $column, $filter);
		[ $query, $params ] = $statement->select();

		return $this->execute($query, $params);
	}

	public function Query(string $table): Query
	{
		return new Query($this, $table);
	}

	public static function create(array $config): MySQL
	{
		return new MySQL($config);
	}
}
