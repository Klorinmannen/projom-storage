<?php

declare(strict_types=1);

namespace Projom\Storage\SQL\Statement;

use Stringable;

use Projom\Storage\SQL\Component\Set;
use Projom\Storage\SQL\Component\Table;
use Projom\Storage\SQL\StatementInterface;
use Projom\Storage\SQL\QueryObject;
use Projom\Storage\SQL\Util;

class Insert implements StatementInterface, Stringable
{
	private readonly Table $table;
	private readonly Set $set;

	public function __construct(QueryObject $queryInsert)
	{
		$this->table = Table::create($queryInsert->collections);
		$this->set = Set::create($queryInsert->fieldsWithValues);
	}

	public function __toString(): string
	{
		[$statement, $params] = $this->statement();
		return $statement;
	}

	public static function create(QueryObject $queryInsert): Insert
	{
		return new Insert($queryInsert);
	}

	public function statement(): array
	{
		$positionalFields = $this->set->positionalFields();
		$positionalParams = $this->set->positionalParams();

		$queryParts[] = "INSERT INTO {$this->table} ({$positionalFields}) VALUES";

		$paramParts = [];
		foreach ($positionalParams as $params)
			$paramParts[] = "({$params})";

		$queryParts[] = Util::join($paramParts, ', ');

		$query = Util::join($queryParts, ' ');
		$params = $this->set->positionalParamValues() ?: null;

		return [
			$query,
			$params
		];
	}
}
