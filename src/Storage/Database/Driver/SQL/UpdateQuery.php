<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Driver\SQL;

use Projom\Storage\Database\Driver\SQL\Filter;
use Projom\Storage\Database\Driver\SQL\Set;
use Projom\Storage\Database\Driver\SQL\Table;
use Projom\Storage\Database\Driver\QueryInterface;
use Projom\Storage\Database\Query\QueryObject;

class UpdateQuery implements QueryInterface
{
	private Table $table;
	private Set $set;
	private Filter $filter;

	public function __construct(QueryObject $queryUpdate)
	{
		$this->table = Table::create($queryUpdate->collections);
		$this->set = Set::create($queryUpdate->fieldsWithValues);
		$this->filter = Filter::create($queryUpdate->filters);
	}

	public static function create(QueryObject $queryUpdate): UpdateQuery
	{
		return new UpdateQuery($queryUpdate);
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
