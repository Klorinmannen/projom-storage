<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Driver\SQL;

use Projom\Storage\Database\Driver\SQL\Filter;
use Projom\Storage\Database\Driver\SQL\Table;
use Projom\Storage\Database\Driver\QueryInterface;
use Projom\Storage\Database\Query\QueryObject;

class DeleteQuery implements QueryInterface
{
	private Table $table;
	private Filter $filter;

	public function __construct(QueryObject $queryDelete)
	{
		$this->table = Table::create($queryDelete->collections);
		$this->filter = Filter::create($queryDelete->filters);
	}

	public static function create(QueryObject $queryDelete): DeleteQuery
	{
		return new DeleteQuery($queryDelete);
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
