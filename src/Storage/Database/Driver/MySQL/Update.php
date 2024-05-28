<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Driver\MySQL;

use Projom\Storage\Database\Driver\MySQL\Filter;
use Projom\Storage\Database\Driver\MySQL\Set;
use Projom\Storage\Database\Driver\MySQL\Table;
use Projom\Storage\Database\Driver\QueryInterface;
use Projom\Storage\Database\Query\Update as QueryUpdate;

class Update implements QueryInterface
{
	private Table $table;
	private Set $set;
	private Filter $filter;

	public function __construct(QueryUpdate $queryUpdate)
	{
		$this->table = Table::create($queryUpdate->collections);
		$this->set = Set::create($queryUpdate->fieldsWithValues);
		$this->filter = Filter::create($queryUpdate->filters);
	}

	public static function create(QueryUpdate $queryUpdate): Update
	{
		return new Update($queryUpdate);
	}

	public function query(): array
	{
		$query = "UPDATE {$this->table} SET {$this->set}";

		if (!$this->filter->empty())
			$query .= " WHERE {$this->filter}";

		return [
			$query,
			($this->set->params() + $this->filter->params()) ?: null
		];
	}
}
