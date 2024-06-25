<?php

declare(strict_types=1);

namespace Projom\Storage;

use Projom\Storage\Database\Engine;
use Projom\Storage\Database\Query;

class DB extends Engine
{
	public static function query(string $table): Query
	{
		return static::dispatch(collections: [ $table ]);
	}

	public static function sql(string $query, array|null $params = null): mixed
	{
		return static::dispatch(query: $query, params: $params);
	}
}
