<?php

declare(strict_types=1);

namespace JRF\Storage\Internal\Database\SQL\Statement;

use Stringable;

use JRF\Storage\Internal\Database\SQL\Component\Column;
use JRF\Storage\Internal\Database\SQL\Component\Filter;
use JRF\Storage\Internal\Database\SQL\Component\Group;
use JRF\Storage\Internal\Database\SQL\Component\Join;
use JRF\Storage\Internal\Database\SQL\Component\Limit;
use JRF\Storage\Internal\Database\SQL\Component\Offset;
use JRF\Storage\Internal\Database\SQL\Component\Order;
use JRF\Storage\Internal\Database\SQL\Component\Table;
use JRF\Storage\Internal\Database\SQL\Statement\StatementInterface;
use JRF\Storage\Internal\Database\SQL\Statement\DTO;
use JRF\Storage\Internal\Database\SQL\Util;

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
