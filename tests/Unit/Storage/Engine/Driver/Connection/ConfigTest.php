<?php

declare(strict_types=1);

namespace Projom\Tests\Unit\Storage\Engine\Driver\Connection;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Engine\Driver\Connection\Config;

class ConfigTest extends TestCase
{
	public static function hasDSNProvider(): array
	{
		return [
			['dsn' => 'mysql:host=localhost;dbname=test', 'expected' => true],
			['dsn' => null, 'expected' => false],
		];
	}

	#[Test]
	#[DataProvider('hasDSNProvider')]
	public function hasDSN(null|string $dsn, bool $expected): void
	{
		$config = new Config([
			'dsn' => $dsn,
		]);

		$this->assertEquals($expected, $config->hasDSN());
	}

	public static function hasNameProvider(): array
	{
		return [
			['name' => 'mysql', 'expected' => true],
			['name' => null, 'expected' => false],
		];
	}

	#[Test]
	#[DataProvider('hasNameProvider')]
	public function hasName(null|string $name, bool $expected): void
	{
		$config = new Config([
			'name' => $name,
		]);

		$this->assertEquals($expected, $config->hasName());
	}

	public function construct(): void
	{
		$config = new Config([]);
		$this->assertEquals(false, $config->hasDSN());
		$this->assertEquals(false, $config->hasName());
	}
}
