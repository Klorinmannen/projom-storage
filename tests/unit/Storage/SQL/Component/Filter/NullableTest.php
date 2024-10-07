<?php

declare(strict_types=1);

namespace Projom\test\unit\Storage\SQL\Component\Filter;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\SQL\Component\Column;
use Projom\Storage\SQL\Component\Filter\Nullable;
use Projom\Storage\SQL\Util\Operator;

class NullableTest extends TestCase
{
	public static function createProvider(): array
	{
		return [
			[
				Column::create(['UserID']),
				Operator::IS_NULL,
				'expected' => [
					'`UserID` IS NULL',
					[]
				]
			],
			[
				Column::create(['UserID']),
				Operator::IS_NOT_NULL,
				'expected' => [
					'`UserID` IS NOT NULL',
					[]
				]
			]
		];
	}

	#[Test]
	#[DataProvider('createProvider')]
	public function create(Column $column, Operator $operator, array $expected): void 
	{
		$actual = Nullable::create($column, $operator);
		$this->assertEquals($expected, $actual);
	}
}
