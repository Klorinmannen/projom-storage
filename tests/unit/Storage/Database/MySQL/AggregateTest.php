<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\MySQL;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\MySQL\Aggregate;

class AggregateTest extends TestCase
{
	#[Test]
	public function cases(): void
	{
		$expected = [
			Aggregate::COUNT,
			Aggregate::MIN,
			Aggregate::MAX,
			Aggregate::AVG,
			Aggregate::SUM
		];
		$actual = Aggregate::cases();
		$this->assertEquals($expected, $actual);
	}

	#[Test]
	public function values(): void
	{
		$expected = ['COUNT', 'MIN', 'MAX', 'AVG', 'SUM'];
		$actual = Aggregate::values();
		$this->assertEquals($expected, $actual);
	}

	public static function buildProvider(): array
	{
		return [
			[
				Aggregate::COUNT,
				'Name',
				'',
				'COUNT(Name)'
			],
			[
				Aggregate::MIN,
				'Age',
				'',
				'MIN(Age)'
			],
			[
				Aggregate::MAX,
				'Age',
				'AgeMax',
				'MAX(Age) AS AgeMax'
			],
			[
				Aggregate::AVG,
				'Age',
				'',
				'AVG(Age)'
			],
			[
				Aggregate::SUM,
				'Age',
				'',
				'SUM(Age)'
			]
		];
	}

	#[Test]
	#[dataProvider('buildProvider')]
	public function build(Aggregate $aggregate, string $field, string $alias, string $expected): void
	{
		$actual = $aggregate->buildSQL($field, $alias);
		$this->assertEquals($expected, $actual);
	}
}