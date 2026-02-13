<?php

declare(strict_types=1);

namespace JRF\Tests\Unit\Storage\Internal\Engine\Connection;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use JRF\Storage\Internal\Engine\Connection\PDOConnection;

class PDOConnectionTest extends TestCase
{
	public static function constructProvider(): array
	{
		return [
			[
				'name',
				'sqlite::memory:',
				null,
				null,
				[
					'PDO::ATTR_ERRMODE' => 'PDO::ERRMODE_EXCEPTION',
					'PDO::ATTR_DEFAULT_FETCH_MODE' => 'PDO::FETCH_ASSOC'
				]
			],
			[1, 'sqlite::memory:', null, null, []]
		];
	}

	#[Test]
	#[DataProvider('constructProvider')]
	public function construct(int|string $name, string $dsn, null|string $username, null|string $password, array $options): void
	{
		$connection = new PDOConnection($name, $dsn, $username, $password, $options);
		$this->assertEquals($name, $connection->name());
	}

	#[Test]
	public function throwsExceptionWhenAttributeIsNotDefined(): void
	{
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('The attribute PDO::ATTR__ERRMODE or value PDO::ERRMODE_EXCEPTION is not a defined constant.');
		new PDOConnection('name', 'sqlite::memory:', null, null, ['PDO::ATTR__ERRMODE' => 'PDO::ERRMODE_EXCEPTION']);
	}
}
