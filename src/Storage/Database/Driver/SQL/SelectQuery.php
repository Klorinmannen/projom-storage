<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Driver\SQL;

use Projom\Storage\Database\Driver\SQL\Column;
use Projom\Storage\Database\Driver\SQL\Filter;
use Projom\Storage\Database\Driver\SQL\Limit;
use Projom\Storage\Database\Driver\SQL\Order;
use Projom\Storage\Database\Driver\SQL\Table;
use Projom\Storage\Database\Driver\SQL\Util;
use Projom\Storage\Database\Driver\QueryInterface;
use Projom\Storage\Database\Query\QueryObject;

class SelectQuery implements QueryInterface
{
	private Table $table;
	private Column $column;
	private Filter $filter;
	private Order $order;
	private Limit $limit;
	private Group $group;

	public function __construct(QueryObject $querySelect)
	{
		$this->table = Table::create($querySelect->collections);
		$this->column = Column::create($querySelect->fields);
		$this->filter = Filter::create($querySelect->filters);
		$this->order = Order::create($querySelect->sorts);
		$this->limit = Limit::create($querySelect->limit);
		$this->group = Group::create($querySelect->groups);
	}

	public static function create(QueryObject $querySelect): SelectQuery
	{
		return new SelectQuery($querySelect);
	}

	public function query(): array
	{
		$queryParts[] = "SELECT {$this->column} FROM {$this->table}";

		if (!$this->filter->empty())
			$queryParts[] = "WHERE {$this->filter}";

		if (!$this->group->empty())
			$queryParts[] = "GROUP BY {$this->group}";

		if (!$this->order->empty())
			$queryParts[] = "ORDER BY {$this->order}";

		if (!$this->limit->empty())
			$queryParts[] = "LIMIT {$this->limit}";

		$query = Util::join($queryParts, ' ');
		$params = $this->filter->params() ?: null;

		return [
			$query,
			$params
		];
	}
}
