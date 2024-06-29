<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Driver\SQL;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Driver\SQL\Order;
use Projom\Storage\Database\Query\Sort;

class OrderTest extends TestCase
{
	public static function create_test_provider(): array
	{
		return [
			[
				[
					['Name', Sort::ASC]
				],
				'`Name` ASC'
			],
			[
				[
					['Name', Sort::ASC,], 
					['Age', Sort::DESC]
				],
				'`Name` ASC, `Age` DESC'
			],
			[
				[
					['UserID', Sort::DESC], 
					['Email', Sort::DESC]
				],
				'`UserID` DESC, `Email` DESC',
			]
		];
	}

	#[DataProvider('create_test_provider')]
	public function test_create(array $sortFields, string $expected)
	{
		$sort = Order::create($sortFields);
		$this->assertEquals($expected, "$sort");
		$this->assertFalse($sort->empty());
	}

	public static function merge_test_provider(): array
	{
		return [
			[
				[
					['Name', Sort::ASC], 
					['Age', Sort::DESC]
				],
				[ 
					['UserID', Sort::DESC], 
					['Email', Sort::DESC]
				],
				'`Name` ASC, `Age` DESC, `UserID` DESC, `Email` DESC'
			]
		];
	}

	#[DataProvider('merge_test_provider')]
	public function test_merge(array $sortFields1, array $sortFields2, string $expected)
	{
		$sort_1 = Order::create($sortFields1);
		$sort_2 = Order::create($sortFields2);
		$sort_1->merge($sort_2);
		$this->assertEquals($expected, "$sort_1");
		$this->assertFalse($sort_1->empty());
	}
}
