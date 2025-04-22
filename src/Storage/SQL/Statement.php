<?php

declare(strict_types=1);

namespace Projom\Storage\SQL;

use Projom\Storage\SQL\QueryObject;
use Projom\Storage\SQL\Statement\Delete;
use Projom\Storage\SQL\Statement\Insert;
use Projom\Storage\SQL\Statement\Select;
use Projom\Storage\SQL\Statement\Update;

class Statement
{
	public function __construct() {}

	public static function create(): Statement
	{
		return new Statement();
	}

	public function select(QueryObject $queryObject): Select
	{
		return Select::create($queryObject);
	}

	public function insert(QueryObject $queryObject): Insert
	{
		return Insert::create($queryObject);
	}

	public function update(QueryObject $queryObject): Update
	{
		return Update::create($queryObject);
	}

	public function delete(QueryObject $queryObject): Delete
	{
		return Delete::create($queryObject);
	}
}
