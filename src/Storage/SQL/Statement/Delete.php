<?php

declare(strict_types=1);

namespace Projom\Storage\SQL\Statement;

use Stringable;

use Projom\Storage\SQL\Component\Filter;
use Projom\Storage\SQL\Component\Join;
use Projom\Storage\SQL\Component\Table;
use Projom\Storage\SQL\Statement\StatementInterface;
use Projom\Storage\SQL\QueryObject;
use Projom\Storage\SQL\Util;

class Delete implements StatementInterface, Stringable
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

	public function __toString(): string
	{
		[$statement, $params] = $this->statement();
		return $statement;
	}

	public static function create(QueryObject $queryDelete): Delete
	{
		return new Delete($queryDelete);
	}

	public function statement(): array
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
