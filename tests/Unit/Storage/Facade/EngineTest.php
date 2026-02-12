<?php

declare(strict_types=1);

namespace JRF\Tests\Unit\Storage\Facade;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use JRF\Storage\Manager as EngineObject;
use JRF\Storage\Facade\Engine;
use JRF\Storage\Query\Action;
use JRF\Storage\Internal\Engine\Driver\Driver;
use JRF\Storage\Internal\Database\SQL\Statement\DTO;

class EngineTest extends TestCase
{
	#[Test]
	public function dispatchNoInstanceException(): void
	{
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage("Engine instance not set");
		$this->expectExceptionCode(400);
		Engine::dispatch(Action::QUERY_BUILDER, args: ['User']);
	}

	// Needs a rework.
	#[Test]
	public function dispatch(): void
	{
		$engine = $this->createMock(EngineObject::class);
		$engine->method('dispatch')->willReturn([]);
		Engine::setInstance($engine);

		$this->expectNotToPerformAssertions();

		$actions = Action::cases();
		foreach ($actions as $action) {

			$value = null;
			if ($action === Action::EXECUTE)
				$value = ['query', ['params']];
			elseif ($action ===  Action::QUERY_BUILDER)
				$value = [['User'], null];
			elseif ($action === Action::CHANGE_CONNECTION)
				$value = 'default';
			else
				$value = new DTO(collections: ['User'], fields: ['Name']);

			Engine::dispatch($action, args: $value);
		}
	}

	#[Test]
	public function useDriver(): void
	{
		$engine = $this->createMock(EngineObject::class);
		Engine::setInstance($engine);

		$this->expectNotToPerformAssertions();
		Engine::useDriver(Driver::MySQL);
	}

	#[Test]
	public function useDriverException(): void
	{
		Engine::reset();

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage("Engine instance not set");
		$this->expectExceptionCode(400);
		Engine::useDriver(Driver::MySQL);
	}
}
