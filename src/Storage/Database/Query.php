<?php

declare(strict_types=1);

namespace Projom\Storage\Database;

use Projom\Storage\Database\Engine;
use Projom\Storage\Database\Action;

class DB
{
	public static function query(string $collection): mixed
	{
		return Engine::dispatch(Action::QUERY, args: [$collection]);
	}

	public static function execute(array $args): mixed
	{
		return Engine::dispatch(Action::EXECUTE, args: $args);
	}

	public static function run(Action $action, array $args): mixed
	{
		return Engine::dispatch($action, args: $args);
	}
}
