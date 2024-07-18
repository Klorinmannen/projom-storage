<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Engine\Driver\Language\SQL\Query;

use Projom\Storage\Database\Engine\Driver\Language\SQL\Set;
use Projom\Storage\Database\Engine\Driver\Language\SQL\Table;
use Projom\Storage\Database\Engine\Driver\Language\QueryInterface;
use Projom\Storage\Database\Query\QueryObject;

class Insert implements QueryInterface
{
	private readonly Table $table;
	private readonly Set $set;

	public function __construct(QueryObject $queryInsert)
	{
		$this->table = Table::create($queryInsert->collections);
		$this->set = Set::create($queryInsert->fieldsWithValues);
	}

	public static function create(QueryObject $queryInsert): Insert
	{
		return new Insert($queryInsert);
	}

	public function query(): array
	{
		$positionalFields = $this->set->positionalFields();
		$positionalParams = $this->set->positionalParams();

		$query = "INSERT INTO {$this->table} ({$positionalFields}) VALUES ({$positionalParams})";
		$params = $this->set->positionalParamValues() ?: null;

		return [
			$query,
			$params
		];
	}
}
