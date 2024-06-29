<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Driver\SQL;

use Projom\Storage\Database\Driver\SQL\Set;
use Projom\Storage\Database\Driver\SQL\Table;
use Projom\Storage\Database\Driver\QueryInterface;
use Projom\Storage\Database\Query\QueryObject;

class InsertQuery implements QueryInterface
{
	private Table $table;
	private Set $set;

	public function __construct(QueryObject $queryInsert)
	{
		$this->table = Table::create($queryInsert->collections);
		$this->set = Set::create($queryInsert->fieldsWithValues);
	}

	public static function create(QueryObject $queryInsert): InsertQuery
	{
		return new InsertQuery($queryInsert);
	}

	public function query(): array
	{
		$positionalFields = $this->set->positionalFields();
		$positionalParams = $this->set->positionalParams();

		$query = "INSERT INTO {$this->table} ({$positionalFields}) VALUES ({$positionalParams})";

		return [
			$query,
			$this->set->positionalParamValues() ?: null
		];
	}
}
