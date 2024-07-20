<?php

declare(strict_types=1);

namespace Projom\Storage;

use Projom\Storage\Database\Engine;
use Projom\Storage\Database\Query;

class DB
{
	public static function query(string $table): Query
	{
		return Engine::dispatch(collections: [$table]);
	}

	public static function sql(string $query, array|null $params = null): mixed
	{
		return Engine::dispatch(query: $query, params: $params);
	}

	public static function startTransaction(): void
	{
		Engine::driver()->startTransaction();
	}

	public static function endTransaction(): void
	{
		Engine::driver()->endTransaction();
	}

	public static function revertTransaction(): void
	{
		Engine::driver()->revertTransaction();
	}
}
