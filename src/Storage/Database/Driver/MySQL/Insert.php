<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Driver\MySQL;

use Projom\Storage\Database\Driver\MySQL\Set;
use Projom\Storage\Database\Driver\MySQL\Table;
use Projom\Storage\Database\Driver\QueryInterface;
use Projom\Storage\Database\Query\Insert as QueryInsert;

class Insert implements QueryInterface
{
	private Table $table;
	private Set $set;

	public function __construct(QueryInsert $queryInsert)
	{
		$this->table = Table::create($queryInsert->collections);
		$this->set = Set::create($queryInsert->fieldsWithValues);
	}

	public static function create(QueryInsert $queryInsert): Insert
	{
		return new Insert($queryInsert);
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
