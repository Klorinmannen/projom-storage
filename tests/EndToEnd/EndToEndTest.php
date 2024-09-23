<?php

declare(strict_types=1);

namespace Projom\tests\EndToEnd;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Engine;
use Projom\Storage\Database\Query\Join;
use Projom\Storage\Database\Query\Operator;
use Projom\Storage\Database\Query\Sort;
use Projom\Storage\DB;

class EndToEndTest extends TestCase
{
	public function setUp(): void
	{
		$config = [
			'driver' => 'mysql',
			'username' => 'projom',
			'password' => 'projom',
			'host' => 'localhost',
			#'host' => '192.168.2.6',
			'port' => 3306,
			'database' => 'EndToEnd'
		];

		Engine::start();
		Engine::loadDriver($config);
	}

	#[Test]
	public function fetch(): void
	{
		$users = DB::query('User')->fetch('UserID', 1);
		$this->assertNotEmpty($users);

		$users = DB::query('User')->fetch('Username', '%Sofie%', Operator::LIKE);
		$this->assertNotEmpty($users);
	}

	#[Test]
	public function test(): void
	{
		$users = DB::query('User')->select('*');
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

		[$user] = DB::query('User')->filterOn('UserID', 2)->get('Firstname');
		$actualFirstname = $user['Firstname'] ?? '';
		$expectedFirstname = 'John';
		$this->assertEquals($expectedFirstname, $actualFirstname);

		$expectedFields = ['Firstname'];
		$actualFields = array_keys($user);
		$this->assertEquals($expectedFields, $actualFields);

		$users = DB::query('User')
			->filterOn('Active', 0, Operator::NE)
			->filterOn('Firstname', '%e', Operator::LIKE)
			->select();
		$actualRecords = count($user);
		$expectedRecords = 1;
		$this->assertEquals($expectedRecords, $actualRecords);

		$records = DB::query('User')
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

		$records = DB::query('User')
			->joinOn('User.UserID', Join::INNER, 'UserRole.UserID')
			->joinOn('UserRole.RoleID', Join::INNER, 'Role.RoleID')
			->filterOn('UserRole.RoleID', 3)
			->sortOn(['User.UserID' => Sort::DESC])
			->select('User.Username', 'UserRole.UserID', 'UserRole.RoleID', 'Role.Role');
		$actualRecords = count($records);
		$expectedRecords = 3;
		$this->assertEquals($expectedRecords, $actualRecords);

		/*
		$actualCount = DB::query('User')->groupOn('Firstname', 'Lastname')->count();
		$expectedCount = 2;
		$this->assertEquals($expectedCount, $actualCount);
		*/

		// Add user
		$newUser = [
			'Username' => 'newuser',
			'Password' => 'newpassword',
			'Firstname' => 'New',
			'Lastname' => 'User',
			'Active' => 0
		];
		$userID = DB::query('User')->insert($newUser);
		$this->assertGreaterThan(0, $userID);

		// Find added user
		[$user] = DB::query('User')
			->filterOn('UserID', $userID)
			->filterOn('Active', 0)
			->get('Active');
		$this->assertNotEmpty($user);

		// Update user
		$affectedRows = DB::query('User')->filterOn('UserID', $userID)->update(['Active' => 1]);
		$expectedRows = 1;
		$this->assertEquals($expectedRows, $affectedRows);

		// Find updated user
		[$user] = DB::query('User')
			->filterOn('UserID', $userID)
			->filterOn('Active', 0, Operator::NE)
			->get();
		$this->assertNotEmpty($user);

		// Delete user
		$affectedRows = DB::query('User')->filterOn('UserID', $userID)->delete();
		$expectedRows = 1;
		$this->assertEquals($expectedRows, $affectedRows);

		// Try to find deleted user
		$affectedRows = DB::query('User')->filterOn('UserID', $userID)->delete();
		$expectedRows = 0;
		$this->assertEquals($expectedRows, $affectedRows);

		$filterLists = [
			'UserID' => [2, 3, 4, 5],
			'Active' => [1]
		];
		$users = DB::query('User')->filterOnList($filterLists, Operator::IN)->select();
		$actualRecords = count($users);
		$expectedRecords = 2;
		$this->assertEquals($expectedRecords, $actualRecords);
	}
}
