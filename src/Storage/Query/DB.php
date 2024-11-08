<?php

declare(strict_types=1);

namespace Projom\Storage\Query;

use Projom\Storage\Engine;
use Projom\Storage\Query\Action;

class DB
{
	public static function query(string $collection, null|array $options = null): mixed
	{
		return Engine::dispatch(Action::QUERY, args: [[$collection], $options]);
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
