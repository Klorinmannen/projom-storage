<?php

declare(strict_types=1);

namespace Projom\Storage;

use Projom\Storage\Database\Engine;
use Projom\Storage\Database\QueryInterface;

class DB extends Engine 
{
	public static function query(string $table): QueryInterface
	{
		return static::dispatch($table);
	}

	public static function sql(string $query, ?array $params = null): mixed
	{
		return static::dispatch($query, $params);
	}
}
