<?php

declare(strict_types=1);

namespace Projom\Tests\Unit\Storage\Engine\Driver;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Engine\Driver\Connection\Config;
use Projom\Storage\Engine\Driver\ConnectionFactory;

class ConnectionFactoryStub extends ConnectionFactory
{
	private \PDO $pdo;

	public function __construct(\PDO $pdo)
	{
		$this->pdo = $pdo;
	}

	public function PDO(
		string $dsn,
		string $username = null,
		string $password = null,
		array $parsedAttributes = []
	): \PDO {
		return $this->pdo;
	}
}

class ConnectionFactoryTest extends TestCase
{
	#[Test]
	public function createPDO(): void
	{
		$pdo = $this->createMock(\PDO::class);
		$connectionFactory = new ConnectionFactoryStub($pdo);
	
		$config = new Config([
			'dsn' => 'mysql:host=localhost;port=3306;dbname=test;charset=utf8mb4',
			'host' => 'localhost',
			'port' => '3306',
			'database' => 'test',
			'charset' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'options' => [
				'ATTR_ERRMODE' => 'ERRMODE_EXCEPTION',
				'ATTR_DEFAULT_FETCH_MODE' => 'FETCH_ASSOC'
			]
		]);

		$pdo = $connectionFactory->createPDO($config);
		$this->assertInstanceOf(\PDO::class, $pdo);
	}

	#[Test]
	public function createPDO_exception(): void
	{
		$sourceFactory = ConnectionFactory::create();
		$config = new Config([
			'host' => 'localhost',
			'port' => '3306',
			'database' => 'test',
			'charset' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci'
		]);

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Connection config is missing dsn');
		$this->expectExceptionCode(400);
		$sourceFactory->createPDO($config);
	}
}
