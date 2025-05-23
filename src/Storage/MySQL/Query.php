<?php

declare(strict_types=1);

namespace Projom\Storage\MySQL;

use Projom\Storage\Engine;
use Projom\Storage\Engine\Driver\Driver;
use Projom\Storage\Query\Action;
use Projom\Storage\Query\Util;
use Projom\Storage\SQL\Statement\Builder;

class Query
{
	private Engine $engine;

	public function __construct(Engine $engine)
	{
		$this->engine = $engine;
	}

	public static function create(Engine $engine): Query
	{
		return new Query($engine);
	}

	public function build(string|array $collections, array $options = []): Builder
	{
		$collections = Util::stringToArray($collections);
		return $this->engine->dispatch(Action::QUERY, Driver::MySQL, [$collections, $options]);
	}

	public function sql(string $sql, null|array $params = null): mixed
	{
		return $this->engine->dispatch(Action::EXECUTE, Driver::MySQL, [$sql, $params]);
	}

	public function useConnection(int|string $name): void
	{
		$this->engine->dispatch(Action::CHANGE_CONNECTION, Driver::MySQL, $name);
	}

	public function startTransaction(): void
	{
		$this->engine->dispatch(Action::START_TRANSACTION, Driver::MySQL);
	}

	public function endTransaction(): void
	{
		$this->engine->dispatch(Action::END_TRANSACTION, Driver::MySQL);
	}

	public function revertTransaction(): void
	{
		$this->engine->dispatch(Action::REVERT_TRANSACTION, Driver::MySQL);
	}
}
