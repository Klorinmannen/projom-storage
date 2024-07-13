<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Engine\Driver\Language;

use Projom\Storage\Database\Engine\Driver\Language\SQL\Query\Delete;
use Projom\Storage\Database\Engine\Driver\Language\SQL\Query\Insert;
use Projom\Storage\Database\Engine\Driver\Language\SQL\Query\Select;
use Projom\Storage\Database\Engine\Driver\Language\SQL\Query\Update;
use Projom\Storage\Database\Query\QueryObject;

class SQL
{
	public static function select(QueryObject $queryObject): Select
	{
		return Select::create($queryObject);
	}

	public static function update(QueryObject $queryObject): Update
	{
		return Update::create($queryObject);
	}

	public static function insert(QueryObject $queryObject): Insert
	{
		return Insert::create($queryObject);
	}

	public static function delete(QueryObject $queryObject): Delete
	{
		return Delete::create($queryObject);
	}
}