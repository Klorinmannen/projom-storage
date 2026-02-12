<?php

declare(strict_types=1);

namespace JRF\Storage\MySQL;

use JRF\Storage\Manager;
use JRF\Storage\Engine\Driver\Driver;
use JRF\Storage\Query\Action;
use JRF\Storage\Query\Util;
use JRF\Storage\SQL\Statement\Builder;

class Query
{
	private Manager $manager;

	public function __construct(Manager $manager)
	{
		$this->manager = $manager;
	}

	public static function create(Manager $manager): Query
	{
		return new Query($manager);
	}

	public function build(string|array $collections, array $options = []): Builder
	{
		$collections = Util::stringToArray($collections);
		return $this->manager->dispatch(Action::QUERY, Driver::MySQL, [$collections, $options]);
	}

	public function sql(string $sql, null|array $params = null): mixed
	{
		return $this->manager->dispatch(Action::EXECUTE, Driver::MySQL, [$sql, $params]);
	}

	public function useConnection(int|string $name): void
	{
		$this->manager->dispatch(Action::CHANGE_CONNECTION, Driver::MySQL, $name);
	}

	public function startTransaction(): void
	{
		$this->manager->dispatch(Action::START_TRANSACTION, Driver::MySQL);
	}

	public function endTransaction(): void
	{
		$this->manager->dispatch(Action::END_TRANSACTION, Driver::MySQL);
	}

	public function revertTransaction(): void
	{
		$this->manager->dispatch(Action::REVERT_TRANSACTION, Driver::MySQL);
	}
}
