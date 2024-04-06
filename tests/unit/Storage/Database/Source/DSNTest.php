<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Source;

use Exception;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Projom\Storage\Database\Source\DSN;

class DSNTest extends TestCase
{
	use DSN;

	public function test_DSN(): void
	{
		$config = [
			'driver' => 'mysql',
			'host' => 'localhost',
			'port' => '3306',
			'dbname' => 'testdb',
			'charset' => 'utf8',
		];

		$expectedDSN = 'mysql:host=localhost;port=3306;dbname=testdb;charset=utf8';

		$this->assertEquals($expectedDSN, static::DSN($config));
	}

	public static function missing_config_provider(): array
	{
		return [
			'missing config' => [[]],
			'missing driver' => [
				[
					'host' => 'localhost',
					'port' => '3306',
					'dbname' => 'testdb',
					'charset' => 'utf8',
				],
			],
			'missing host' => [
				[
					'driver' => 'mysql',
					'port' => '3306',
					'dbname' => 'testdb',
					'charset' => 'utf8',
				],
			],
			'missing port' => [
				[
					'driver' => 'mysql',
					'host' => 'localhost',
					'dbname' => 'testdb',
					'charset' => 'utf8',
				],
			],
			'missing dbname' => [
				[
					'driver' => 'mysql',
					'host' => 'localhost',
					'port' => '3306',
					'charset' => 'utf8',
				],
			],
		];
	}

	#[dataProvider('missing_config_provider')]
	public function test_missing_config(array $config): void
	{
		$this->expectException(Exception::class);
		static::DSN($config);
	}
}
