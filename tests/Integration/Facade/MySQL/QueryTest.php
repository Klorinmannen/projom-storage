<?php

declare(strict_types=1);

namespace Projom\tests\Integration\Facade\MySQL;

include_once __DIR__ . '/../../UserRecord.php';

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Tests\Integration\UserRecord;

use Projom\Storage\Engine as EngineObject;
use Projom\Storage\Facade\Engine;
use Projom\Storage\Facade\MySQL\Query;
use Projom\Storage\Query\Format;
use Projom\Storage\SQL\Util\Join;
use Projom\Storage\SQL\Util\Operator;
use Projom\Storage\SQL\Util\Sort;

class QueryTest extends TestCase
{
	public function setUp(): void
	{
		$config = [
			'driver' => 'mysql',
			'options' => [],
			'connections' => [
				[
					'username' => 'projom',
					'password' => 'projom',
					'host' => 'localhost',
					'port' => 3306,
					'database' => 'Integration'
				]
			]
		];

		Engine::setInstance(EngineObject::create($config));
	}

	public static function queryOptionsProvider(): array
	{
		return [
			[
				['return_single_record' => true]
			]
		];
	}

	#[Test]
	#[DataProvider('queryOptionsProvider')]
	public function queryOptions(array $options): void
	{
		$user = Query::build('User', $options)
			->filterOn('UserID', 1)
			->select('Username');
		$this->assertArrayHasKey('Username', $user);
	}

	#[Test]
	public function fetch(): void
	{
		$users = Query::build('User')->fetch('UserID', 1);
		$this->assertNotEmpty($users);

		$users = Query::build('User')->fetch('Username', '%Sofie%', Operator::LIKE);
		$this->assertNotEmpty($users);
	}

	#[Test]
	public function selectAll(): void
	{
		$users = Query::build('User')->select('*');
		$actualRecords = count($users);
		$expectedRecords = 5;
		$this->assertEquals($expectedRecords, $actualRecords);
	}

	#[Test]
	public function selectWithFilter(): void
	{
		$users = Query::build('User')
			->filterOn('Active', 0, Operator::NE)
			->filterOn('Firstname', '%e', Operator::LIKE)
			->select();

		$expectedRecords = 1;
		$this->assertEquals($expectedRecords, count($users));
	}

	#[Test]
	public function selectWithJoinFilterSort(): void
	{
		$records = Query::build('User')
			->joinOn('User.UserID', Join::INNER, 'UserRole.UserID')
			->joinOn('UserRole.RoleID', Join::INNER, 'Role.RoleID')
			->filterOn('UserRole.RoleID', 3)
			->sortOn(['User.UserID' => Sort::DESC])
			->select('User.Username', 'UserRole.UserID', 'UserRole.RoleID', 'Role.Role');

		$expectedRecords = 3;
		$this->assertEquals($expectedRecords, count($records));
	}

	#[Test]
	public function selectWithJoinFilterSortLimit(): void
	{
		$records = Query::build('User')
			->joinOn('User.UserID', Join::INNER, 'UserRole.UserID')
			->joinOn('UserRole.RoleID', Join::INNER, 'Role.RoleID')
			->filterOn('UserRole.RoleID', 3)
			->sortOn(['User.UserID' => Sort::DESC])
			->limit(1)
			->select('User.Username', 'UserRole.UserID', 'UserRole.RoleID', 'Role.Role');

		$expectedRecords = 1;
		$this->assertEquals($expectedRecords, count($records));

		$fields = array_keys(array_pop($records));
		$expectedFields = ['Username', 'UserID', 'RoleID', 'Role'];
		$this->assertEquals($expectedFields, $fields);
	}

	#[Test]
	public function crud(): void
	{
		// Add new user
		$newUser = [
			'Username' => 'newuser',
			'Password' => 'newpassword',
			'Firstname' => 'New',
			'Lastname' => 'User',
			'Active' => 0
		];
		$userID = Query::build('User')->insert($newUser);
		$this->assertGreaterThan(0, $userID);

		// Find new user
		$user = Query::build('User', ['return_single_record' => true])
			->filterOn('UserID', $userID)
			->select();
		$this->assertNotEmpty($user);

		// Update new user
		$affectedRows = Query::build('User')->filterOn('UserID', $userID)->update(['Active' => 1]);
		$expectedRows = 1;
		$this->assertEquals($expectedRows, $affectedRows);

		// Delete user
		$affectedRows = Query::build('User')->filterOn('UserID', $userID)->delete();
		$expectedRows = 1;
		$this->assertEquals($expectedRows, $affectedRows);
	}

	#[Test]
	public function selectAsCustomObject(): void
	{
		$user = Query::build('User', ['return_single_record' => true])
			->formatAs(Format::CUSTOM_OBJECT, UserRecord::class)
			->filterOn('UserID', 1)
			->select();

		$this->assertInstanceOf(UserRecord::class, $user);
	}

	#[Test]
	public function selectNoRecordsFound(): void
	{
		$result = Query::build('UserRole')->filterOn('UserID', 0)->select();
		$this->assertEquals(null, $result);
	}
}
