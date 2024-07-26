<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Query;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Query\LogicalOperator;
use Projom\Storage\Database\Query\Operator;
use Projom\Storage\Database\Query\Filter;

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
		$filter = Filter::buildGroup(...$filters);
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
				Filter::buildGroup(['Name' => 'John', 'Username' => 'Doe'], Operator::NE),
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
}
