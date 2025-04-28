<?php

declare(strict_types=1);

namespace Projom\Tests\EndToEnd;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Engine;
use Projom\Storage\Logger\LoggerType;
use Projom\Storage\Logger\SimpleLogger;
use Projom\Storage\MySQL\Query;
use Projom\Tests\EndToEnd\User;

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

		$user = new User();
		$user->invoke($query);

		$user->all();
		$user->find(1);
		$clonedUser = $user->clone(1);
		$user->update($clonedUser['UserID'], ['Username' => 'Sofie']);
		$user->delete($clonedUser['UserID']);

		$log = $logger->logStore();
		$this->assertNotEmpty($log);
	}
}
