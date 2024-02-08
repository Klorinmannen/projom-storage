<?php

declare(strict_types=1);

namespace Tests\Unit\Storage;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Dsn;

class DsnTest extends TestCase
{
	public static function provider_test_createString(): array
	{
		return [
			[
				'config' => [
					'server_host' => 'localhost',
					'server_port' => '3306',
					'name' => 'test'
				],
				'expected' => 'mysql:host=localhost;port=3306;dbname=test'
			]
		];
	}

	#[DataProvider('provider_test_createString')]
	public function test_createString(array $config, string $expected): void
	{
		$this->assertEquals($expected, Dsn::createString($config));
	}

	public static function provider_test_parseConfig(): array
	{
		return [
			[
				'config' => [
					'server_host' => 'localhost',
					'server_port' => '3306',
					'name' => 'test'
				],
				'expected' => [
					'localhost',
					'3306',
					'test'
				]
			]
		];
	}

	#[DataProvider('provider_test_parseConfig')]
	public function test_parseConfig(array $config, array $expected): void
	{
		$this->assertEquals($expected, Dsn::parseConfig($config));
	}

	public static function provider_test_buildDsn(): array
	{
		return [
			[
				'serverHost' => 'localhost',
				'serverPort' => '3306',
				'databaseName' => 'test',
				'expected' => 'mysql:host=localhost;port=3306;dbname=test'
			]
		];
	}

	#[DataProvider('provider_test_buildDsn')]
	public function test_buildDsn(
		string $serverHost,
		string $serverPort,
		string $databaseName,
		string $expected
	): void {
		$actual = Dsn::buildDsn($serverHost, $serverPort, $databaseName);
		$this->assertEquals($expected, $actual);
	}
}
