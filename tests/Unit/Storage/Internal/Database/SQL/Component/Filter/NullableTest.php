<?php

declare(strict_types=1);

namespace JRF\Tests\Unit\Storage\Internal\Database\SQL\Component\Filter;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use JRF\Storage\Database\Util\Operator;
use JRF\Storage\Internal\Database\SQL\Component\Column;
use JRF\Storage\Internal\Database\SQL\Component\Filter\Nullable;

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
