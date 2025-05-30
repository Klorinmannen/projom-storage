<?php

declare(strict_types=1);

namespace Projom\Storage\SQL\Statement;

use Stringable;

use Projom\Storage\SQL\Component\Column;
use Projom\Storage\SQL\Component\Filter;
use Projom\Storage\SQL\Component\Group;
use Projom\Storage\SQL\Component\Join;
use Projom\Storage\SQL\Component\Limit;
use Projom\Storage\SQL\Component\Offset;
use Projom\Storage\SQL\Component\Order;
use Projom\Storage\SQL\Component\Table;
use Projom\Storage\SQL\Statement\StatementInterface;
use Projom\Storage\SQL\Statement\DTO;
use Projom\Storage\SQL\Util;

class Select implements StatementInterface, Stringable
{
	private readonly Table $table;
	private readonly Column $column;
	private readonly Join $join;
	private readonly Filter $filter;
	private readonly Group $group;
	private readonly Order $order;
	private readonly Limit $limit;
	private readonly Offset $offset;

	public function __construct(DTO $querySelect)
	{
		$this->table = Table::create($querySelect->collections);
		$this->column = Column::create($querySelect->fields);
		$this->join = Join::create($querySelect->joins);
		$this->filter = Filter::create($querySelect->filters);
		$this->group = Group::create($querySelect->groups);
		$this->order = Order::create($querySelect->sorts);
		$this->limit = Limit::create($querySelect->limit);
		$this->offset = Offset::create($querySelect->offset);
	}

	public function __toString(): string
	{
		[$statement, $params] = $this->statement();
		return $statement;
	}

	public static function create(DTO $querySelect): Select
	{
		return new Select($querySelect);
	}

	public function statement(): array
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

		if (!$this->offset->empty())
			$queryParts[] = "OFFSET {$this->offset}";

		$query = Util::join($queryParts, ' ');
		$params = $this->filter->params() ?: null;

		return [
			$query,
			$params
		];
	}
}
