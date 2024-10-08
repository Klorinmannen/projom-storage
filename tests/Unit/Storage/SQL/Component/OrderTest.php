<?php

declare(strict_types=1);

namespace Projom\Tests\Unit\Storage\SQL\Component;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\SQL\Component\Order;
use Projom\Storage\SQL\Util\Sort;

class OrderTest extends TestCase
{
	public static function create_test_provider(): array
	{
		return [
			[
				[],
				''
			],			
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
			],
			[
				[
					['UserRole.Role', Sort::ASC], 
					['User.Name', Sort::DESC]
				],
				'`UserRole`.`Role` ASC, `User`.`Name` DESC'
			]			
		];
	}

	#[Test]
	#[DataProvider('create_test_provider')]
	public function create(array $sortFields, string $expected)
	{
		$sort = Order::create($sortFields);
		$this->assertEquals($expected, "$sort");
	}

	#[Test]
	public function empty()
	{
		$sort = Order::create([]);
		$this->assertTrue($sort->empty());
	}
}
