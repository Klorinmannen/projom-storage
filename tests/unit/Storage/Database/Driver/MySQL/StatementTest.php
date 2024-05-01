<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Driver\MySQL;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\LogicalOperators;
use Projom\Storage\Database\Operators;
use Projom\Storage\Database\Driver\MySQL\Column;
use Projom\Storage\Database\Driver\MySQL\Filter;
use Projom\Storage\Database\Driver\MySQL\Statement;
use Projom\Storage\Database\Driver\MySQL\Table;

class StatementTest extends TestCase
{
	public static function select_test_provider(): array
	{
		return [
			'Default filter' => [
				'User',
				['Name'],
				[
					['Name' => 'John'],
					Operators::EQ,
					LogicalOperators::AND
				],
				[
					'query' => 'SELECT `Name` FROM `User` WHERE `Name` = :filter_name_1',
					'params' => ['filter_name_1' => 'John']
				]
			],
			'Null filter' => [
				'User',
				['Name'],
				[
					['Name' => null],
					Operators::IS_NULL,
					LogicalOperators::AND
				],
				[
					'query' => 'SELECT `Name` FROM `User` WHERE `Name` IS NULL',
					'params' => null
				]
			],
			'IN filter' => [
				'User',
				['*'],
				[
					['Age' => [ 12, 23, 45]],
					Operators::IN,
					LogicalOperators::AND
				],
				[
					'query' => 'SELECT * FROM `User` WHERE `Age` IN ( :filter_age_1_1, :filter_age_1_2, :filter_age_1_3 )',
					'params' => ['filter_age_1_1' => 12, 'filter_age_1_2' => 23, 'filter_age_1_3' => 45]
				]
			]
		];
	}

	#[DataProvider('select_test_provider')]
	public function test_select(string $table, array $columns, array $filter, array $expected): void
	{
		$table = new Table($table);
		
		$column = new Column($columns);
		$column->parse();

		$filter = new Filter(...$filter);
		$filter->parse();

		[$query, $params] = Statement::select($table, $column, $filter);

		$this->assertEquals($expected['query'], $query);
		$this->assertEquals($expected['params'], $params);
	}
}
