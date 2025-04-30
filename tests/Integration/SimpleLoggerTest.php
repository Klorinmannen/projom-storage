<?php

declare(strict_types=1);

namespace Projom\Tests\Integration;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Engine;
use Projom\Storage\Logger\LoggerType;
use Projom\Storage\Logger\SimpleLogger;
use Projom\Storage\MySQL\Query;
use Projom\Tests\Integration\UserRepository;

class SimpleLoggerTest extends TestCase
{
	#[Test]
	public function logger(): void
	{
		$logger = new SimpleLogger(LoggerType::LOG_STORE);

		$config = [
			'driver' => 'mysql',
			'options' => [],
			'logger' => $logger,
			'connections' => [
				[
					'username' => 'projom',
					'password' => 'projom',
					'host' => 'localhost',
					'port' => 3306,
					'database' => 'EndToEnd'
				]
			]
		];

		$engine = Engine::create($config);
		$query = Query::create($engine);

		$userRepo = new UserRepository();
		$userRepo->invoke($query);

		// Do some database operations, which will hopefully be logged
		$userRepo->all();
		$userRepo->find(1);
		$clonedUser = $userRepo->clone(1);
		$userRepo->update($clonedUser['UserID'], ['Username' => 'Sofie']);
		$userRepo->delete($clonedUser['UserID']);

		// Check if theres anything in the log
		$log = $logger->logStore();
		$this->assertNotEmpty($log);
	}
}
