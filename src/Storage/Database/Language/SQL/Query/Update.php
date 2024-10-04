<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Language\SQL\Query;

use Projom\Storage\Database\Language\QueryInterface;
use Projom\Storage\Database\Language\SQL\Filter;
use Projom\Storage\Database\Language\SQL\Set;
use Projom\Storage\Database\Language\SQL\Table;
use Projom\Storage\Database\Language\SQL\Join;
use Projom\Storage\Database\Language\SQL\QueryObject;
use Projom\Storage\Database\Language\SQL\Util;

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
		$queryParts[] = "UPDATE {$this->table} SET {$this->set}";

		if (!$this->join->empty())
			$queryParts[] = "{$this->join}";

		if (!$this->filter->empty())
			$queryParts[] = "WHERE {$this->filter}";

		$query = Util::join($queryParts, ' ');
		$params = ($this->set->params() + $this->filter->params()) ?: null;

		return [
			$query,
			$params
		];
	}
}
