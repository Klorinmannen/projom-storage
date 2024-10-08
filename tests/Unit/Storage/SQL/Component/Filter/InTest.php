<?php

declare(strict_types=1);

namespace Projom\Tests\Unit\Storage\SQL\Component\Filter;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\SQL\Component\Column;
use Projom\Storage\SQL\Component\Filter\In;
use Projom\Storage\SQL\Util\Operator;

class InTest extends TestCase
{
	public static function createProvider(): array
	{
		return [
			[
				Column::create(['UserID']),
				Operator::IN,
				[1, 2, 3],
				1,
				'expected' => [
					'`UserID` IN ( :filter_userid_1_1, :filter_userid_1_2, :filter_userid_1_3 )',
					[
						'filter_userid_1_1' => 1,
						'filter_userid_1_2' => 2,
						'filter_userid_1_3' => 3
					]
				]
			],
			[
				Column::create(['UserID']),
				Operator::NOT_IN,
				[1, 2, 3],
				1,
				'expected' => [
					'`UserID` NOT IN ( :filter_userid_1_1, :filter_userid_1_2, :filter_userid_1_3 )',
					[
						'filter_userid_1_1' => 1,
						'filter_userid_1_2' => 2,
						'filter_userid_1_3' => 3
					]
				]
			]
		];
	}

	#[Test]
	#[DataProvider('createProvider')]
	public function create(Column $column, Operator $operator, array $values, int $filterID, array $expected): void
	{
		$actual = In::create($column, $operator, $values, $filterID);
		$this->assertEquals($expected, $actual);
	}
}
