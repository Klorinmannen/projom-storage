<?php

declare(strict_types=1);

namespace JRF\Storage\Internal\Database\SQL\Statement;

use Stringable;

use JRF\Storage\Internal\Database\SQL\Component\Filter;
use JRF\Storage\Internal\Database\SQL\Component\Join;
use JRF\Storage\Internal\Database\SQL\Component\Table;
use JRF\Storage\Internal\Database\SQL\Statement\StatementInterface;
use JRF\Storage\Internal\Database\SQL\Statement\DTO;
use JRF\Storage\Internal\Database\SQL\Util;

class Delete implements StatementInterface, Stringable
{
	private readonly Table $table;
	private readonly Join $join;
	private readonly Filter $filter;

	public function __construct(DTO $queryDelete)
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

	public static function create(DTO $queryDelete): Delete
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
