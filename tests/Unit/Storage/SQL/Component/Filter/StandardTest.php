<?php

declare(strict_types=1);

namespace JRF\Tests\Unit\Storage\SQL\Component\Filter;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use JRF\Storage\Internal\Database\SQL\Component\Column;
use JRF\Storage\Internal\Database\SQL\Component\Filter\Standard;
use JRF\Storage\Database\Util\Operator;

class StandardTest extends TestCase
{
	public static function createProvider(): array
	{
		return [
			[
				Column::create(['UserID']),
				Operator::EQ,
				1,
				1,
				'expected' => [
					'`UserID` = :filter_userid_1',
					[
						'filter_userid_1' => 1
					]
				]
			],
			[
				Column::create(['UserID']),
				Operator::NE,
				1,
				1,
				'expected' => [
					'`UserID` <> :filter_userid_1',
					[
						'filter_userid_1' => 1
					]
				]
			]
		];
	}

	#[Test]
	#[DataProvider('createProvider')]
	public function create(Column $column, Operator $operator, mixed $value, int $filterID, array $expected): void
	{
		$actual = Standard::create($column, $operator, $value, $filterID);
		$this->assertEquals($expected, $actual);
	}
}
