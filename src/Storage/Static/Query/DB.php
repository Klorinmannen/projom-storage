<?php

declare(strict_types=1);

namespace Projom\Storage\Static\Query;

use Projom\Storage\Static\Engine;
use Projom\Storage\Query\Action;
use Projom\Storage\Query\Util;

class DB
{
	public static function query(string|array $collections, null|array $options = null): mixed
	{
		$collections = Util::stringToArray($collections);
		return Engine::dispatch(Action::QUERY, args: [$collections, $options]);
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
