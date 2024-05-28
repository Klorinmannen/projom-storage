<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Driver\MySQL;

use Projom\Storage\Database\Driver\MySQL\Column;
use Projom\Storage\Database\Driver\MySQL\Filter;
use Projom\Storage\Database\Driver\MySQL\Limit;
use Projom\Storage\Database\Driver\MySQL\Order;
use Projom\Storage\Database\Driver\MySQL\Table;
use Projom\Storage\Database\Driver\QueryInterface;
use Projom\Storage\Database\Query\Select as QuerySelect;

class Select implements QueryInterface
{
	private Table $table;
	private Column $column;
	private Filter $filter;
	private Order $order;
	private Limit $limit;

	public function __construct(QuerySelect $querySelect)
	{
		$this->table = Table::create($querySelect->collections);
		$this->column = Column::create($querySelect->fields);
		$this->filter = Filter::create($querySelect->filters);
		$this->order = Order::create($querySelect->order);
		$this->limit = Limit::create($querySelect->limit);
	}

	public static function create(QuerySelect $querySelect): Select
	{
		return new Select($querySelect);
	}

	public function query(): array
	{
		$query = "SELECT {$this->column} FROM {$this->table}";

		if (!$this->filter->empty())
			$query .= " WHERE {$this->filter}";

		if (!$this->order->empty())
			$query .= " ORDER BY {$this->order}";

		if (!$this->limit->empty())
			$query .= " LIMIT {$this->limit}";

		return [
			$query,
			$this->filter->params() ?: null
		];
	}
}
