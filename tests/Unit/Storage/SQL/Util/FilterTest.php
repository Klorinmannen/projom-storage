<?php

declare(strict_types=1);

namespace Projom\Tests\Unit\Storage\SQL\Util;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\SQL\Util\Filter;
use Projom\Storage\SQL\Util\LogicalOperator;
use Projom\Storage\SQL\Util\Operator;

class FilterTest extends TestCase
{
	public static function buildProvider(): array
	{
		return [
			[
				['Name', 'John', Operator::NE, LogicalOperator::AND],
				['Name', Operator::NE, 'John', LogicalOperator::AND]
			],
			[
				['Name', 'John', Operator::NE],
				['Name', Operator::NE, 'John', LogicalOperator::AND]
			],
			[
				['Name', 'John'],
				['Name', Operator::EQ, 'John', LogicalOperator::AND]
			]
		];
	}

	#[Test]
	#[DataProvider('buildProvider')]
	public function build(array $filters, array $expected): void
	{
		$filter = Filter::build(...$filters);
		$this->assertEquals($expected, $filter);
	}

	public static function buildGroupProvider(): array
	{
		return [
			[
				'filters' => [
					['Name' => '', 'Password' => '']
				],
				'expected' => [
					['Name', Operator::EQ, '', LogicalOperator::AND],
					['Password', Operator::EQ, '', LogicalOperator::AND]
				]
			],
			[
				'filters' => [
					['Name' => '', 'Password' => '',],
					Operator::NE
				],
				'expected' => [
					['Name', Operator::NE, '', LogicalOperator::AND],
					['Password', Operator::NE, '', LogicalOperator::AND]
				]
			],
			[
				'filters' => [
					['Name' => '', 'Password' => '',],
					Operator::NE,
					LogicalOperator::OR
				],
				'expected' => [
					['Name', Operator::NE, '', LogicalOperator::OR],
					['Password', Operator::NE, '', LogicalOperator::OR]
				]
			]
		];
	}

	#[Test]
	#[DataProvider('buildGroupProvider')]
	public function buildGroup(array $filters, array $expected): void
	{
		$filter = Filter::list(...$filters);
		$this->assertEquals($expected, $filter);
	}

	public static function combineProvider(): array
	{
		return [
			[
				[
					Filter::build('Name', 'John'),
					Filter::build('Username', 'Doe', Operator::IS_NOT_NULL),
					Filter::build('Age', 25, logicalOperator: LogicalOperator::OR)
				],
				[
					['Name', Operator::EQ, 'John', LogicalOperator::AND],
					['Username', Operator::IS_NOT_NULL, 'Doe', LogicalOperator::AND],
					['Age', Operator::EQ, 25, LogicalOperator::OR]
				]
			],
			[
				Filter::list(['Name' => 'John', 'Username' => 'Doe'], Operator::NE),
				[
					['Name', Operator::NE, 'John', LogicalOperator::AND],
					['Username', Operator::NE, 'Doe', LogicalOperator::AND]
				]
			]
		];
	}

	#[Test]
	#[DataProvider('combineProvider')]
	public function combine(array $filters, array $expected): void
	{
		$filter = Filter::combine(...$filters);
		$this->assertEquals($expected, $filter);
	}

	#[Test]
	public function eq(): void
	{
		$actual = Filter::eq('Name', 'John');
		$expected = ['Name', Operator::EQ, 'John', LogicalOperator::AND];
		$this->assertEquals($expected, $actual);
	}

	#[Test]
	public function ne(): void
	{
		$actual = Filter::ne('Name', 'John');
		$expected = ['Name', Operator::NE, 'John', LogicalOperator::AND];
		$this->assertEquals($expected, $actual);
	}

	#[Test]
	public function gt(): void
	{
		$actual = Filter::gt('Age', 25);
		$expected = ['Age', Operator::GT, 25, LogicalOperator::AND];
		$this->assertEquals($expected, $actual);
	}

	#[Test]
	public function gte(): void
	{
		$actual = Filter::gte('Age', 25);
		$expected = ['Age', Operator::GTE, 25, LogicalOperator::AND];
		$this->assertEquals($expected, $actual);
	}

	#[Test]
	public function lt(): void
	{
		$actual = Filter::lt('Age', 25);
		$expected = ['Age', Operator::LT, 25, LogicalOperator::AND];
		$this->assertEquals($expected, $actual);
	}

	#[Test]
	public function lte(): void
	{
		$actual = Filter::lte('Age', 25);
		$expected = ['Age', Operator::LTE, 25, LogicalOperator::AND];
		$this->assertEquals($expected, $actual);
	}

	#[Test]
	public function like(): void
	{
		$actual = Filter::like('Name', 'John');
		$expected = ['Name', Operator::LIKE, 'John', LogicalOperator::AND];
		$this->assertEquals($expected, $actual);
	}

	#[Test]
	public function notLike(): void
	{
		$actual = Filter::notLike('Name', 'John');
		$expected = ['Name', Operator::NOT_LIKE, 'John', LogicalOperator::AND];
		$this->assertEquals($expected, $actual);
	}

	#[Test]
	public function in(): void
	{
		$actual = Filter::in('UserID', [1, 2, 3]);
		$expected = ['UserID', Operator::IN, [1, 2, 3], LogicalOperator::AND];
		$this->assertEquals($expected, $actual);
	}

	#[Test]
	public function notIn(): void
	{
		$actual = Filter::notIn('UserID', [1, 2, 3]);
		$expected = ['UserID', Operator::NOT_IN, [1, 2, 3], LogicalOperator::AND];
		$this->assertEquals($expected, $actual);
	}

	#[Test]
	public function isNulls(): void
	{
		$actual = Filter::isNull('Name');
		$expected = ['Name', Operator::IS_NULL, null, LogicalOperator::AND];
		$this->assertEquals($expected, $actual);
	}

	#[Test]
	public function isNotNull(): void
	{
		$actual = Filter::isNotNull('Name');
		$expected = ['Name', Operator::IS_NOT_NULL, null, LogicalOperator::AND];
		$this->assertEquals($expected, $actual);
	}

	#[Test]
	public function between(): void
	{
		$actual = Filter::between('Age', 18, 25);
		$expected = ['Age', Operator::BETWEEN, [18, 25], LogicalOperator::AND];
		$this->assertEquals($expected, $actual);
	}

	#[Test]
	public function notBetween(): void
	{
		$actual = Filter::notBetween('Age', 18, 25);
		$expected = ['Age', Operator::NOT_BETWEEN, [18, 25], LogicalOperator::AND];
		$this->assertEquals($expected, $actual);
	}
}
