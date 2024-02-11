<?php

declare(strict_types=1);

namespace Projom\Storage\Query\Builder;

use Projom\Storage\Source\PDO;
use Projom\Storage\Query\Builder\Sql\Condition;
use Projom\Storage\Query\Builder\Sql\Util;
use Projom\Storage\Query\Builder\Sql\Select;
use Projom\Storage\Query\Builder\Sql\Where;

class Driver
{
	public static function select(
		array $columns,
		string $table,
		array $conditions,
		array $opts
	): mixed 
	{
		$select = Select::build($columns);
		$table = Util::quote($table);

		$sqlConditionList = Condition::buildList($conditions);
		$where = Where::build($sqlConditionList);

		$statement = "$select FROM $table $where";
		$params = Where::namedParameterList($sqlConditionList);

		$pdo = PDO::get();
		if (!$query = $pdo->prepare($statement))
			throw new \Exception('Internal server error', 500);
		if (!$query->execute($params))
			throw new \Exception('Internal server error', 500);

		if (!$result = $query->fetchAll())
			return [];

		return $result;
	}
}