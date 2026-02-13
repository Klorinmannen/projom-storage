<?php

declare(strict_types=1);

namespace JRF\Storage\Database;

use JRF\Storage\Internal\Database\Action;
use JRF\Storage\Internal\Database\Util;
use JRF\Storage\Internal\Database\SQL\Statement\Builder;
use JRF\Storage\Internal\Registry;

class Query
{
	public static function build(string|array $collections, array $options = []): Builder
	{
		$collections = Util::stringToArray($collections);
		$manager = Registry::get();
		return $manager->dispatch(Action::QUERY_BUILDER, args: [$collections, $options]);
	}

	public static function sql(string $sql, null|array $params = null): mixed
	{
		$manager = Registry::get();
		return $manager->dispatch(Action::EXECUTE, args: [$sql, $params]);
	}

	public static function useConnection(int|string $name): void
	{
		$manager = Registry::get();
		$manager->dispatch(Action::CHANGE_CONNECTION, args: $name);
	}

	public static function startTransaction(): void
	{
		$manager = Registry::get();
		$manager->dispatch(Action::START_TRANSACTION);
	}

	public static function endTransaction(): void
	{
		$manager = Registry::get();
		$manager->dispatch(Action::END_TRANSACTION);
	}

	public static function revertTransaction(): void
	{
		$manager = Registry::get();
		$manager->dispatch(Action::REVERT_TRANSACTION);
	}
}
