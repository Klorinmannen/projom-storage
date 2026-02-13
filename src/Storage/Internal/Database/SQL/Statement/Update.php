<?php

declare(strict_types=1);

namespace JRF\Storage\Internal\Database\SQL\Statement;

use Stringable;

use JRF\Storage\Internal\Database\SQL\Component\Filter;
use JRF\Storage\Internal\Database\SQL\Component\Join;
use JRF\Storage\Internal\Database\SQL\Component\Set;
use JRF\Storage\Internal\Database\SQL\Component\Table;
use JRF\Storage\Internal\Database\SQL\Statement\StatementInterface;
use JRF\Storage\Internal\Database\SQL\Statement\DTO;
use JRF\Storage\Internal\Database\SQL\Util;

class Update implements StatementInterface, Stringable
{
	private readonly Table $table;
	private readonly Set $set;
	private readonly Join $join;
	private readonly Filter $filter;

	public function __construct(DTO $queryUpdate)
	{
		$this->table = Table::create($queryUpdate->collections);
		$this->set = Set::create($queryUpdate->fieldsWithValues);
		$this->join = Join::create($queryUpdate->joins);
		$this->filter = Filter::create($queryUpdate->filters);
	}

	public function __toString(): string
	{
		[$statement, $params] = $this->statement();
		return $statement;
	}

	public static function create(DTO $queryUpdate): Update
	{
		return new Update($queryUpdate);
	}

	public function statement(): array
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
