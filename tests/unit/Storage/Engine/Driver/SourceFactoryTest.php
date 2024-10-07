<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Engine\Driver;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Engine\Config;
use Projom\Storage\Engine\Driver\SourceFactory;

class SourceFactoryStub extends SourceFactory
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

class SourceFactoryTest extends TestCase
{
	#[Test]
	public function createPDO(): void
	{
		$pdo = $this->createMock(\PDO::class);
		$sourceFactory = new SourceFactoryStub($pdo);
	
		$config = new Config([
			'driver' => 'mysql',
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

		$pdo = $sourceFactory->createPDO($config);
		$this->assertInstanceOf(\PDO::class, $pdo);
	}

	#[Test]
	public function createPDO_exception(): void
	{
		$sourceFactory = SourceFactory::create();
		$config = new Config([
			'driver' => 'unknown', // Unknown driver
			'host' => 'localhost',
			'port' => '3306',
			'database' => 'test',
			'charset' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci'
		]);

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Driver is not supported');
		$this->expectExceptionCode(400);
		$sourceFactory->createPDO($config);
	}
}
