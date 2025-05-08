<?php

declare(strict_types=1);

namespace Projom\tests\Integration\Static\MySQL;

include_once __DIR__ . '/../../UserRecord.php';

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Query\Format;
use Projom\Storage\SQL\Util\Join;
use Projom\Storage\SQL\Util\Operator;
use Projom\Storage\SQL\Util\Sort;
use Projom\Storage\Static\Engine;
use Projom\Storage\Static\MySQL\Query;
use Projom\Tests\Integration\UserRecord;

class EndToEndTest extends TestCase
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

		Engine::start();
		Engine::loadDriver($config);
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
	public function test(): void
	{
		$users = Query::build('User')->select('*');
		$actualRecords = count($users);
		$expectedRecords = 5;
		$this->assertEquals($expectedRecords, $actualRecords);

		$user = array_pop($users);
		$actualFields = array_keys($user);
		$expectedFields = [
			'UserID',
			'Username',
			'Password',
			'Firstname',
			'Lastname',
			'Active',
			'Created',
			'Updated'
		];
		$this->assertEquals($expectedFields, $actualFields);

		[$user] = Query::build('User')->filterOn('UserID', 2)->get('Firstname');
		$actualFirstname = $user['Firstname'] ?? '';
		$expectedFirstname = 'John';
		$this->assertEquals($expectedFirstname, $actualFirstname);

		$expectedFields = ['Firstname'];
		$actualFields = array_keys($user);
		$this->assertEquals($expectedFields, $actualFields);

		$users = Query::build('User')
			->filterOn('Active', 0, Operator::NE)
			->filterOn('Firstname', '%e', Operator::LIKE)
			->select();
		$actualRecords = count($user);
		$expectedRecords = 1;
		$this->assertEquals($expectedRecords, $actualRecords);

		$records = Query::build('User')
			->joinOn('User.UserID', Join::INNER, 'UserRole.UserID')
			->joinOn('UserRole.RoleID', Join::INNER, 'Role.RoleID')
			->filterOn('UserRole.RoleID', 3)
			->sortOn(['User.UserID' => Sort::DESC])
			->limit(1)
			->select('User.Username', 'UserRole.UserID', 'UserRole.RoleID', 'Role.Role');
		$actualRecords = count($records);
		$expectedRecords = 1;
		$this->assertEquals($expectedRecords, $actualRecords);

		$record = array_pop($records);
		$actualFields = array_keys($record);
		$expectedFields = ['Username', 'UserID', 'RoleID', 'Role'];
		$this->assertEquals($expectedFields, $actualFields);

		$actualUserID = $record['UserID'];
		$expectedUserID = 5;
		$this->assertEquals($expectedUserID, $actualUserID);

		$records = Query::build('User')
			->joinOn('User.UserID', Join::INNER, 'UserRole.UserID')
			->joinOn('UserRole.RoleID', Join::INNER, 'Role.RoleID')
			->filterOn('UserRole.RoleID', 3)
			->sortOn(['User.UserID' => Sort::DESC])
			->select('User.Username', 'UserRole.UserID', 'UserRole.RoleID', 'Role.Role');
		$actualRecords = count($records);
		$expectedRecords = 3;
		$this->assertEquals($expectedRecords, $actualRecords);

		// Add user
		$newUser = [
			'Username' => 'newuser',
			'Password' => 'newpassword',
			'Firstname' => 'New',
			'Lastname' => 'User',
			'Active' => 0
		];
		$userID = Query::build('User')->insert($newUser);
		$this->assertGreaterThan(0, $userID);

		// Find added user
		[$user] = Query::build('User')
			->filterOn('UserID', $userID)
			->filterOn('Active', 0)
			->get('Active');
		$this->assertNotEmpty($user);

		// Update user
		$affectedRows = Query::build('User')->filterOn('UserID', $userID)->update(['Active' => 1]);
		$expectedRows = 1;
		$this->assertEquals($expectedRows, $affectedRows);

		// Find updated user
		[$user] = Query::build('User')
			->filterOn('UserID', $userID)
			->filterOn('Active', 0, Operator::NE)
			->get();
		$this->assertNotEmpty($user);

		// Delete user
		$affectedRows = Query::build('User')->filterOn('UserID', $userID)->delete();
		$expectedRows = 1;
		$this->assertEquals($expectedRows, $affectedRows);

		// Try to find deleted user
		$affectedRows = Query::build('User')->filterOn('UserID', $userID)->delete();
		$expectedRows = 0;
		$this->assertEquals($expectedRows, $affectedRows);

		$filterLists = [
			'UserID' => [2, 3, 4, 5],
			'Active' => [1]
		];
		$users = Query::build('User')->filterOnFields($filterLists, Operator::IN)->select();
		$actualRecords = count($users);
		$expectedRecords = 2;
		$this->assertEquals($expectedRecords, $actualRecords);

		$users = Query::build('User')->formatAs(Format::CUSTOM_OBJECT, UserRecord::class)->filterOn('UserID', [1, 3], Operator::BETWEEN)->select();
		$actualRecords = count($users);
		$expectedRecords = 3;
		$this->assertEquals($expectedRecords, $actualRecords);

		// No records found, should return null
		Query::build('UserRole')->delete();
		$actualRecords = Query::build('UserRole')->select();
		$expectedRecords = null;
		$this->assertEquals($expectedRecords, $actualRecords);
	}
}
