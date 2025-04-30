<?php

declare(strict_types=1);

namespace Projom\Storage\SQL;

use Projom\Storage\SQL\Statement\DTO;
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

	public function select(DTO $queryObject): Select
	{
		return Select::create($queryObject);
	}

	public function insert(DTO $queryObject): Insert
	{
		return Insert::create($queryObject);
	}

	public function update(DTO $queryObject): Update
	{
		return Update::create($queryObject);
	}

	public function delete(DTO $queryObject): Delete
	{
		return Delete::create($queryObject);
	}
}
