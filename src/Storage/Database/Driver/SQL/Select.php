<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Driver\SQL;

use Projom\Storage\Database\Driver\SQL\Column;
use Projom\Storage\Database\Driver\SQL\Filter;
use Projom\Storage\Database\Driver\SQL\Limit;
use Projom\Storage\Database\Driver\SQL\Order;
use Projom\Storage\Database\Driver\SQL\Table;
use Projom\Storage\Database\Driver\QueryInterface;
use Projom\Storage\Database\Query\QueryObject;

class Select implements QueryInterface
{
	private Table $table;
	private Column $column;
	private Filter $filter;
	private Order $order;
	private Limit $limit;

	public function __construct(QueryObject $querySelect)
	{
		$this->table = Table::create($querySelect->collections);
		$this->column = Column::create($querySelect->fields);
		$this->filter = Filter::create($querySelect->filters);
		$this->order = Order::create($querySelect->sorts);
		$this->limit = Limit::create($querySelect->limit);
	}

	public static function create(QueryObject $querySelect): Select
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
