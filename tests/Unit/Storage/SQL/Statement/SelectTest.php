<?php

declare(strict_types=1);

namespace Projom\Tests\Unit\Storage\SQL\Statement;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\SQL\Statement\DTO;
use Projom\Storage\SQL\Statement\Select;
use Projom\Storage\SQL\Util\Filter;
use Projom\Storage\SQL\Util\Join;
use Projom\Storage\SQL\Util\LogicalOperator;
use Projom\Storage\SQL\Util\Sort;

class SelectTest extends TestCase
{
	public static function createProvider(): array
	{
		return [
			[
				new DTO(
					collections: ['User'],
					fields: ['UserID', 'Name'],
					joins: [['User.UserID', Join::INNER, 'Log.UserID']],
					filters: [
						[
							Filter::list(['UserID' => 10, 'Log.RequestType' => 'GET']),
							LogicalOperator::AND
						]
					],
					sorts: [['UserID', Sort::ASC], ['Name', Sort::DESC]],
					limit: 10,
					groups: [['Name']]
				),
				[
					'SELECT `UserID`, `Name` FROM `User` INNER JOIN `Log` ON `User`.`UserID` = `Log`.`UserID`' .
						' WHERE ( `UserID` = :filter_userid_1 AND `Log`.`RequestType` = :filter_log_requesttype_2 )' .
						' GROUP BY `Name` ORDER BY `UserID` ASC, `Name` DESC LIMIT 10',
					[
						'filter_userid_1' => 10,
						'filter_log_requesttype_2' => 'GET'
					]
				]
			],
			[
				new DTO(
					collections: ['User'],
					fields: ['*'],
					sorts: [['UserID', Sort::ASC], ['Name', Sort::DESC]]
				),
				[
					'SELECT * FROM `User` ORDER BY `UserID` ASC, `Name` DESC',
					null
				]
			],
			[
				new DTO(
					collections: ['User'],
					fields: ['*'],
					limit: 10,
					offset: 5
				),
				[
					'SELECT * FROM `User` LIMIT 10 OFFSET 5',
					null
				]
			],
			[
				new DTO(
					collections: ['User'],
					fields: ['*']
				),
				[
					'SELECT * FROM `User`',
					null
				]
			],
		];
	}

	#[Test]
	#[DataProvider('createProvider')]
	public function create(DTO $queryObject, array $expected): void
	{
		$select = Select::create($queryObject);
		$this->assertEquals($expected, $select->statement());
	}

	#[Test]
	public function stringable(): void
	{
		$queryObject = new DTO(
			collections: ['User'],
			fields: ['UserID', 'Name'],
			joins: [['User.UserID', Join::INNER, 'Log.UserID']],
			filters: [
				[
					Filter::list(['UserID' => 10, 'Log.RequestType' => 'GET']),
					LogicalOperator::AND
				]
			],
			sorts: [['UserID', Sort::ASC], ['Name', Sort::DESC]],
			limit: 10,
			groups: [['Name']]
		);
		$select = Select::create($queryObject);
		$this->assertEquals(
			'SELECT `UserID`, `Name` FROM `User` INNER JOIN `Log` ON `User`.`UserID` = `Log`.`UserID`' .
				' WHERE ( `UserID` = :filter_userid_1 AND `Log`.`RequestType` = :filter_log_requesttype_2 )' .
				' GROUP BY `Name` ORDER BY `UserID` ASC, `Name` DESC LIMIT 10',
			(string) $select
		);
	}
}
