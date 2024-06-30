<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Engine\Driver\Language\SQL\Query;

use Projom\Storage\Database\Engine\Driver\Language\SQL\Filter;
use Projom\Storage\Database\Engine\Driver\Language\SQL\Table;
use Projom\Storage\Database\Engine\Driver\Language\QueryInterface;
use Projom\Storage\Database\Query\QueryObject;

class Delete implements QueryInterface
{
	private Table $table;
	private Filter $filter;

	public function __construct(QueryObject $queryDelete)
	{
		$this->table = Table::create($queryDelete->collections);
		$this->filter = Filter::create($queryDelete->filters);
	}

	public static function create(QueryObject $queryDelete): Delete
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
