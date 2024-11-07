<?php

declare(strict_types=1);

namespace Projom\Tests\Unit\Storage\Engine\Driver;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Engine\Driver\Config;
use Projom\Storage\Engine\Driver\ConnectionFactory;
use Projom\Storage\Engine\Driver\PDOConnection;

class ConnectionFactoryTest extends TestCase
{
	#[Test]
	public function createPDO(): void
	{
		$connectionFactory = ConnectionFactory::create();
		$config = new Config([
			'name' => 'name',
			'dsn' => 'sqlite::memory:',
			'options' => [
				'PDO::ATTR_ERRMODE' => 'PDO::ERRMODE_EXCEPTION',
				'PDO::ATTR_DEFAULT_FETCH_MODE' => 'PDO::FETCH_ASSOC'
			]
		]);
		$connection = $connectionFactory->PDOConnection($config);
		$this->assertInstanceOf(PDOConnection::class, $connection);
	}
}
