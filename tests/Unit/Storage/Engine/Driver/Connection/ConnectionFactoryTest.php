<?php

declare(strict_types=1);

namespace JRF\Tests\Unit\Storage\Engine\Driver\Connection;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use JRF\Storage\Internal\Engine\Driver\Connection\Config;
use JRF\Storage\Internal\Engine\Driver\Connection\ConnectionFactory;
use JRF\Storage\Engine\Driver\Connection\PDOConnection;

class ConnectionFactoryTest extends TestCase
{
	#[Test]
	public function createPDO(): void
	{
		$connectionFactory = ConnectionFactory::create();

		$connectionConfigs = [
			new Config([
				'name' => 'name',
				'dsn' => 'sqlite::memory:',
				'options' => [
					'PDO::ATTR_ERRMODE' => 'PDO::ERRMODE_EXCEPTION',
					'PDO::ATTR_DEFAULT_FETCH_MODE' => 'PDO::FETCH_ASSOC'
				]
			]),
			new Config([
				'dsn' => 'sqlite::memory:',
				'options' => []
			])
		];

		$connections = $connectionFactory->PDOConnections($connectionConfigs);
		foreach ($connections as $connection)
			$this->assertInstanceOf(PDOConnection::class, $connection);
	}
}
