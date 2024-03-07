<?php

declare(strict_types=1);

namespace Projom\Storage\Database\PDO;

use Projom\Storage\Database\DriverInterface;
use Projom\Storage\Database\Language\Sql\Query\Select;
use Projom\Storage\Database\PDO\Source;
use Projom\Storage\Database\PDO\Query;

class MySQL implements DriverInterface
{
	use Source;

	public function __construct(array $config, $options = [])
	{
		$this->connect($config, $options);
	}

	public function query(string $table): Query
	{
		return new Query($this, $table);
	}

	public function select(string $table, string $column, mixed $value, string $operator): mixed
	{
        $select = Select::create($table, $column, $value, $operator);
		return $this->execute($select->query(), $select->params());
	}
}
