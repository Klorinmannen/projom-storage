<?php

declare(strict_types=1);

namespace Projom\Tests\Unit\Storage\Engine\Driver\Connection;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Engine\Config;
use Projom\Storage\Engine\Driver\Connection\DSN;

class DSNTest extends TestCase
{
	public static function MySQLProvider(): array
	{
		return [
			[ 
				'config' => new Config([ 'driver' => 'mysql', 'host' => 'localhost', 'port' => '3306', 'database' => 'test', 'charset' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci' ]), 
				'expected' => 'mysql:host=localhost;port=3306;dbname=test;charset=utf8mb4;collation=utf8mb4_unicode_ci' 
			],
			[
				'config' => new Config([ 'driver' => 'mysql', 'host' => 'localhost', 'port' => '3306', 'database' => 'test' ]),
				'expected' => 'mysql:host=localhost;port=3306;dbname=test'
			],
			[
				'config' => new Config([ 'dsn' => 'mysql:host=localhost;port=3306;dbname=test;charset=utf8mb4;collation=utf8mb4_unicode_ci' ]),
				'expected' => 'mysql:host=localhost;port=3306;dbname=test;charset=utf8mb4;collation=utf8mb4_unicode_ci'
			]
		];
	}

	#[Test]
	#[DataProvider('MySQLProvider')]	
	public function MySQL(Config $config, string $expected): void
	{
		$result = DSN::MySQL($config);
		$this->assertEquals($expected, $result);
	}

	#[Test]
	public function MySQL_exception_missingHost(): void
	{
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Config is missing host');
		$this->expectExceptionCode(400);

		DSN::MySQL(new Config([ 'port' => '3306', 'database' => 'test' ]));
	}

	#[Test]
	public function MySQL_exception_missingPort(): void
	{
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Config is missing port');
		$this->expectExceptionCode(400);

		DSN::MySQL(new Config([ 'host' => 'localhost', 'database' => 'test' ]));
	}

	#[Test]
	public function MySQL_exception_missingDatabase(): void
	{
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Config is missing database');
		$this->expectExceptionCode(400);

		DSN::MySQL(new Config([ 'host' => 'localhost', 'port' => '3306' ]));
	}
}