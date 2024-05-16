<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Driver\MySQL;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Operators;
use Projom\Storage\Database\Driver\MySQL\Column;
use Projom\Storage\Database\Driver\MySQL\Filter;
use Projom\Storage\Database\Driver\MySQL\Set;
use Projom\Storage\Database\Driver\MySQL\Sort;
use Projom\Storage\Database\Driver\MySQL\Statement;
use Projom\Storage\Database\Driver\MySQL\Table;
use Projom\Storage\Database\LogicalOperators;
use Projom\Storage\Database\Sorts;

class StatementTest extends TestCase
{
	public static function select_test_provider(): array
	{
		return [
			'Default filter' => [
				'User',
				['Name'],
				[
					[
						'Name',
						Operators::EQ,
						'John',
						LogicalOperators::AND
					]
				],
				[],
				[
					'query' => 'SELECT `Name` FROM `User` WHERE `Name` = :filter_name_1',
					'params' => ['filter_name_1' => 'John']
				]
			],
			'Null filter' => [
				'User',
				['Name'],
				[
					[
						'Name',
						Operators::IS_NULL,
						null,
						LogicalOperators::AND
					]
				],
				[],
				[
					'query' => 'SELECT `Name` FROM `User` WHERE `Name` IS NULL',
					'params' => null
				]
			],
			'IN filter' => [
				'User',
				['*'],
				[
					[
						'Age',
						Operators::IN,
						[12, 23, 45],
						LogicalOperators::AND
					]
				],
				['Name' => Sorts::DESC],
				[
					'query' => 'SELECT * FROM `User` WHERE `Age` IN ( :filter_age_1_1, :filter_age_1_2, :filter_age_1_3 ) ORDER BY `Name` DESC',
					'params' => ['filter_age_1_1' => 12, 'filter_age_1_2' => 23, 'filter_age_1_3' => 45]
				]
			]
		];
	}

	#[DataProvider('select_test_provider')]
	public function test_select(string $table, array $columns, array $filter, array $sort, array $expected): void
	{
		$table = Table::create($table);
		$column = Column::create($columns);
		$filter = Filter::create($filter);
		$sort = Sort::create($sort);
		$filter->parse();

		[$query, $params] = Statement::select($table, $column, $filter, $sort);

		$this->assertEquals($expected['query'], $query);
		$this->assertEquals($expected['params'], $params);
	}

	public static function insert_test_provider(): array
	{
		return [
			'Insert single row' => [
				'User',
				['Name' => 'John', 'Age' => 18],
				[
					'query' => 'INSERT INTO `User` (`Name`, `Age`) VALUES (?, ?)',
					'params' => ['John', 18]
				]
			]
		];
	}

	#[DataProvider('insert_test_provider')]
	public function test_insert(string $table, array $values, array $expected): void
	{
		$table = Table::create($table);
		$set = Set::create($values);

		[$query, $params] = Statement::insert($table, $set);

		$this->assertEquals($expected['query'], $query);
		$this->assertEquals($expected['params'], $params);
	}

	public static function update_test_provider(): array
	{
		return [
			'Update single row' => [
				'User',
				['Name' => 'John', 'Age' => 18],
				[
					[
						'Name',
						Operators::EQ,
						'John',
						LogicalOperators::AND
					]
				],
				[
					'query' => 'UPDATE `User` SET `Name` = :set_name_1, `Age` = :set_age_2 WHERE `Name` = :filter_name_1',
					'params' => ['set_name_1' => 'John', 'set_age_2' => 18, 'filter_name_1' => 'John']
				]
			]
		];
	}

	#[DataProvider('update_test_provider')]
	public function test_update(string $table, array $values, array $filter, array $expected): void
	{
		$table = Table::create($table);
		$set = Set::create($values);
		$filter = Filter::create($filter);
		$filter->parse();

		[$query, $params] = Statement::update($table, $set, $filter);

		$this->assertEquals($expected['query'], $query);
		$this->assertEquals($expected['params'], $params);
	}

	public static function delete_test_provider(): array
	{
		return [
			'Delete single row' => [
				'User',
				[
					[
						'Name',
						Operators::EQ,
						'John',
						LogicalOperators::AND
					]
				],
				[
					'query' => 'DELETE FROM `User` WHERE `Name` = :filter_name_1',
					'params' => ['filter_name_1' => 'John']
				]
			]
		];
	}

	#[DataProvider('delete_test_provider')]
	public function test_delete(string $table, array $filter, array $expected): void
	{
		$table = Table::create($table);
		$filter = Filter::create($filter);
		$filter->parse();

		[$query, $params] = Statement::delete($table, $filter);

		$this->assertEquals($expected['query'], $query);
		$this->assertEquals($expected['params'], $params);
	}
}
