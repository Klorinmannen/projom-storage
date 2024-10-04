<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Language\SQL\Query;

use Projom\Storage\Database\Language\QueryInterface;
use Projom\Storage\Database\Language\SQL\Set;
use Projom\Storage\Database\Language\SQL\Table;
use Projom\Storage\Database\Language\SQL\QueryObject;
use Projom\Storage\Database\Language\SQL\Util;

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
