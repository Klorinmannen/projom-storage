<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Query;

use Projom\Storage\Database\Engine;
use Projom\Storage\Database\Engine\Driver;
use Projom\Storage\Database\Query\Action;
use Projom\Storage\Database\MySQL\QueryBuilder;

class MySQL
{
	public static function query(string $collection): QueryBuilder
	{
		return Engine::dispatch(Action::QUERY, Driver::MySQL, [$collection]);
	}

	public static function sql(string $sql, array|null $params = null): mixed
	{
		return Engine::dispatch(Action::EXECUTE, Driver::MySQL, [$sql, $params]);
	}

	public static function startTransaction(): void
	{
		Engine::dispatch(Action::START_TRANSACTION, Driver::MySQL);
	}

	public static function endTransaction(): void
	{
		Engine::dispatch(Action::END_TRANSACTION, Driver::MySQL);
	}

	public static function revertTransaction(): void
	{
		Engine::dispatch(Action::REVERT_TRANSACTION, Driver::MySQL);
	}
}
