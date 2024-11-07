<?php

declare(strict_types=1);

namespace Projom\Storage\Query;

use Projom\Storage\Query\Action;
use Projom\Storage\Engine;
use Projom\Storage\Engine\Driver;
use Projom\Storage\SQL\QueryBuilder;

class MySQL
{
	public static function query(string $collection): QueryBuilder
	{
		return Engine::dispatch(Action::QUERY, Driver::MySQL, [$collection]);
	}

	public static function sql(string $sql, null|array $params = null): mixed
	{
		return Engine::dispatch(Action::EXECUTE, Driver::MySQL, [$sql, $params]);
	}

	public static function useConnection(int|string $name): void
	{
		Engine::dispatch(Action::CHANGE_CONNECTION, Driver::MySQL, $name);
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
