<?php

declare(strict_types=1);

namespace Projom\tests\EndToEnd;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Engine;
use Projom\Storage\Database\Query\Operator;
use Projom\Storage\DB;

class EndToEndTest extends TestCase
{
	public function setUp(): void
	{
		$config = [
			'driver' => 'mysql',
			'username' => 'root',
			'password' => 'root',
			'host' => '127.0.0.1',
			'port' => 3306,
			'database' => 'EndToEnd',
		];

		Engine::start();
		Engine::loadDriver($config);
	}

	#[Test]
	public function fetch()
	{
		$users = DB::query('Users')->fetch('UserID', 1);
		$this->assertNotEmpty($users);

		$users = DB::query('Users')->fetch('Username', '%Sofie%', Operator::LIKE);
		$this->assertNotEmpty($users);
	}
}
