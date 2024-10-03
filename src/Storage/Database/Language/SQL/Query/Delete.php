<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Language\SQL\Query;

use Projom\Storage\Database\Language\SQL\Filter;
use Projom\Storage\Database\Language\SQL\Table;
use Projom\Storage\Database\Language\SQL\Join;
use Projom\Storage\Database\Language\SQL\QueryInterface;
use Projom\Storage\Database\Language\SQL\Util;
use Projom\Storage\Database\MySQL\QueryObject;

class Delete implements QueryInterface
{
	private readonly Table $table;
	private readonly Join $join;
	private readonly Filter $filter;

	public function __construct(QueryObject $queryDelete)
	{
		$this->table = Table::create($queryDelete->collections);
		$this->join = Join::create($queryDelete->joins);
		$this->filter = Filter::create($queryDelete->filters);
	}

	public static function create(QueryObject $queryDelete): Delete
	{
		return new Delete($queryDelete);
	}

	public function query(): array
	{
		$queryParts[] = "DELETE FROM {$this->table}";

		if (!$this->join->empty())
			$queryParts[] = "{$this->join}";

		if (!$this->filter->empty())
			$queryParts[] = "WHERE {$this->filter}";

		$query = Util::join($queryParts, ' ');
		$params = $this->filter->params() ?: null;

		return [
			$query,
			$params
		];
	}
}
