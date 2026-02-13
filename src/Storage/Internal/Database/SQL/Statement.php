<?php

declare(strict_types=1);

namespace JRF\Storage\Internal\Database\SQL;

use JRF\Storage\Internal\Database\SQL\Statement\DTO;
use JRF\Storage\Internal\Database\SQL\Statement\Delete;
use JRF\Storage\Internal\Database\SQL\Statement\Insert;
use JRF\Storage\Internal\Database\SQL\Statement\Select;
use JRF\Storage\Internal\Database\SQL\Statement\Update;

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
