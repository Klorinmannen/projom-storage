<?php

declare(strict_types=1);

namespace JRF\Tests\Integration\Facade\Query;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use JRF\Storage\Manager as EngineObject;
use JRF\Storage\Facade\Engine;
use JRF\Storage\Facade\Query\DB;
use JRF\Storage\Query\Action;

class DBTest extends TestCase
{
	public function setUp(): void
	{
		$config = [
			'driver' => 'mysql',
			'options' => [],
			'connections' => [
				[
					'username' => 'JRF',
					'password' => 'JRF',
					'host' => 'localhost',
					'port' => 3306,
					'database' => 'Integration'
				]
			]
		];

		Engine::setInstance(EngineObject::initialize($config));
	}

	#[Test]
	public function queryWithOptions()
	{
		$user = DB::query('User', ['return_single_record' => true])
			->filterOn('UserID', 1)
			->select('Username');
		$this->assertArrayHasKey('Username', $user);
	}

	#[Test]
	public function execute()
	{
		$actual = DB::execute(['SELECT * FROM User']);
		$this->assertNotEmpty($actual);
	}

	#[Test]
	public function runMethod()
	{
		$actual = DB::run(Action::EXECUTE, ['SELECT * FROM User']);
		$this->assertNotEmpty($actual);
	}
}
