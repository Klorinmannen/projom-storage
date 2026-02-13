<?php

declare(strict_types=1);

namespace JRF\Tests\Unit\Storage\Internal\Engine\Connection;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use JRF\Storage\Internal\Engine\Connection\Config;

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
