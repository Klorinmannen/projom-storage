<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Engine\Driver\Language\SQL\Query;

use Projom\Storage\Database\Engine\Driver\Language\SQL\Filter;
use Projom\Storage\Database\Engine\Driver\Language\SQL\Set;
use Projom\Storage\Database\Engine\Driver\Language\SQL\Table;
use Projom\Storage\Database\Engine\Driver\Language\QueryInterface;
use Projom\Storage\Database\Engine\Driver\Language\SQL\Join;
use Projom\Storage\Database\Query\QueryObject;

class Update implements QueryInterface
{
	private readonly Table $table;
	private readonly Set $set;
	private readonly Join $join;
	private readonly Filter $filter;

	public function __construct(QueryObject $queryUpdate)
	{
		$this->table = Table::create($queryUpdate->collections);
		$this->set = Set::create($queryUpdate->fieldsWithValues);
		$this->join = Join::create($queryUpdate->joins);
		$this->filter = Filter::create($queryUpdate->filters);
	}

	public static function create(QueryObject $queryUpdate): Update
	{
		return new Update($queryUpdate);
	}

	public function query(): array
	{
		$query = "UPDATE {$this->table} SET {$this->set}";

		if (!$this->join->empty())
			$query .= " {$this->join}";

		if (!$this->filter->empty())
			$query .= " WHERE {$this->filter}";

		return [
			$query,
			($this->set->params() + $this->filter->params()) ?: null
		];
	}
}
