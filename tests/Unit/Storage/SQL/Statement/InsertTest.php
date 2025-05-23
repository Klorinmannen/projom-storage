<?php

declare(strict_types=1);

namespace Projom\Tests\Unit\Storage\SQL\Statement;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\SQL\Statement\DTO;
use Projom\Storage\SQL\Statement\Insert;

class InsertTest extends TestCase
{
	public static function createProvider(): array
	{
		return [
			[
				new DTO(
					collections: ['User'],
					fieldsWithValues: [['Name' => 'John', 'Age' => 25]]
				),
				[
					'INSERT INTO `User` (`Name`, `Age`) VALUES (?, ?)',
					['John', 25]
				]
			],
			[
				new DTO(
					collections: ['User'],
					fieldsWithValues: [['Name' => 'John', 'Age' => 25], ['Name' => 'Jane', 'Age' => 30]]
				),
				[
					'INSERT INTO `User` (`Name`, `Age`) VALUES (?, ?), (?, ?)',
					['John', 25, 'Jane', 30]
				]
			]
		];
	}

	#[Test]
	#[DataProvider('createProvider')]
	public function create(DTO $queryObject, array $expected): void
	{
		$insert = Insert::create($queryObject);
		$this->assertEquals($expected, $insert->statement());
	}

	#[Test]
	public function stringable(): void
	{
		$queryObject = new DTO(
			collections: ['User'],
			fieldsWithValues: [['Name' => 'John', 'Age' => 25]]
		);
		$insert = Insert::create($queryObject);
		$this->assertEquals('INSERT INTO `User` (`Name`, `Age`) VALUES (?, ?)', (string) $insert);
	}
}
