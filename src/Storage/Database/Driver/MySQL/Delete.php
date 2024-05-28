<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Driver\MySQL;

use Projom\Storage\Database\Driver\MySQL\Filter;
use Projom\Storage\Database\Driver\MySQL\Table;
use Projom\Storage\Database\Driver\QueryInterface;
use Projom\Storage\Database\Query\Delete as QueryDelete;

class Delete implements QueryInterface
{
	private Table $table;
	private Filter $filter;

	public function __construct(QueryDelete $queryDelete)
	{
		$this->table = Table::create($queryDelete->collections);
		$this->filter = Filter::create($queryDelete->filters);
	}

	public static function create(QueryDelete $queryDelete): Delete
	{
		return new Delete($queryDelete);
	}

	public function query(): array
	{
		$query = "DELETE FROM {$this->table}";

		if (!$this->filter->empty())
			$query .= " WHERE {$this->filter}";

		return [
			$query,
			$this->filter->params() ?: null
		];
	}
}
