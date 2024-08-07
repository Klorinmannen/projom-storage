<?php

declare(strict_types=1);

namespace Projom\Storage;

use Projom\Storage\Database\Engine;
use Projom\Storage\Database\Query;
use Projom\Storage\Database\Query\Action;

class DB
{
	public static function query(string $table): Query
	{
		return Engine::dispatch(Action::QUERY, $table);
	}

	public static function sql(string $query, array|null $params = null): mixed
	{
		return Engine::dispatch(Action::EXECUTE, [$query, $params]);
	}

	public static function startTransaction(): void
	{
		Engine::dispatch(Action::START_TRANSACTION);
	}

	public static function endTransaction(): void
	{
		Engine::dispatch(Action::END_TRANSACTION);
	}

	public static function revertTransaction(): void
	{
		Engine::dispatch(Action::REVERT_TRANSACTION);
	}
}
