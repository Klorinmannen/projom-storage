<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Engine\Source;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Engine\Config;
use Projom\Storage\Database\Engine\Source\SourceFactory;

class SourceFactoryTest extends TestCase
{
	#[Test]
	public function createPDO(): void
	{
		$pdo = $this->createMock(\PDO::class);

		$sourceFactory = $this->createStub(SourceFactory::class);
		$sourceFactory->method('PDO')->willReturn($pdo);

		$config = new Config([
			'driver' => 'mysql',
			'host' => 'localhost',
			'port' => '3306',
			'database' => 'test',
			'charset' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci'
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
