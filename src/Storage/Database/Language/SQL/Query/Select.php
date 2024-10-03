<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Language\SQL\Query;

use Projom\Storage\Database\Language\SQL\Column;
use Projom\Storage\Database\Language\SQL\Filter;
use Projom\Storage\Database\Language\SQL\Group;
use Projom\Storage\Database\Language\SQL\Limit;
use Projom\Storage\Database\Language\SQL\Order;
use Projom\Storage\Database\Language\SQL\Table;
use Projom\Storage\Database\Language\SQL\Join;
use Projom\Storage\Database\Language\SQL\QueryInterface;
use Projom\Storage\Database\Language\SQL\Util;
use Projom\Storage\Database\MySQL\QueryObject;

class Select implements QueryInterface
{
	private readonly Table $table;
	private readonly Column $column;
	private readonly Join $join;
	private readonly Filter $filter;
	private readonly Group $group;
	private readonly Order $order;
	private readonly Limit $limit;

	public function __construct(QueryObject $querySelect)
	{
		$this->table = Table::create($querySelect->collections);
		$this->column = Column::create($querySelect->fields);
		$this->join = Join::create($querySelect->joins);
		$this->filter = Filter::create($querySelect->filters);
		$this->group = Group::create($querySelect->groups);
		$this->order = Order::create($querySelect->sorts);
		$this->limit = Limit::create($querySelect->limit);
	}

	public static function create(QueryObject $querySelect): Select
	{
		return new Select($querySelect);
	}

	public function query(): array
	{
		$queryParts[] = "SELECT {$this->column} FROM {$this->table}";

		if (!$this->join->empty())
			$queryParts[] = "{$this->join}";

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
