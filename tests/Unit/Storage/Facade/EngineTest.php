<?php

declare(strict_types=1);

namespace Projom\Tests\Unit\Storage\Static;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Engine as EngineObject;
use Projom\Storage\Facade\Engine;
use Projom\Storage\Query\Action;
use Projom\Storage\Engine\Driver\Driver;
use Projom\Storage\SQL\Statement\DTO;

class EngineTest extends TestCase
{
	#[Test]
	public function dispatchNoInstanceException(): void
	{
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage("Engine instance not set");
		$this->expectExceptionCode(400);
		Engine::dispatch(Action::QUERY, args: ['User']);
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
			elseif ($action ===  Action::QUERY)
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
