<?php

declare(strict_types=1);

namespace Projom\tests\Integration;

include_once __DIR__ . '/UserRepository.php';

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Engine;
use Projom\Storage\MySQL\Query;
use Projom\Tests\Integration\UserRepository;

class RepositoryTest extends TestCase
{
	private Query $query;

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

		$engine = Engine::create($config);
		$this->query = Query::create($engine);
	}

	#[Test]
	public function crud()
	{
		$userRepo = new UserRepository();
		$userRepo->invoke($this->query, primaryField: 'UserID', table: 'User');

		// Create a new user
		$newUser = [
			'Username' => 'anya.doe@example.com',
			'Firstname' => 'Anya',
			'Lastname' => 'Doe',
			'Password' => 'password'
		];
		$userID = $userRepo->create($newUser);

		// Check if the new user was created
		$userRecord = $userRepo->get('UserID', $userID);
		$this->assertEquals($newUser['Username'], $userRecord['Username']);

		// Update the new user
		$smith = 'Smith';
		$userRepo->update($userID, ['Lastname' => $smith]);

		// Check if the new user was updated
		$userRecord = $userRepo->get('Lastname', $smith);
		$this->assertNotNull($userRecord);

		// Delete the new user
		$userRepo->delete($userID);

		// Check if the new user was deleted
		$userRecord = $userRepo->find($userID);
		$this->assertNull($userRecord);
	}

	#[Test]
	public function find(): void
	{
		$userRepo = new UserRepository();
		$userRepo->invoke($this->query, primaryField: 'UserID', table: 'User');

		$userRecord = $userRepo->find(1);
		$this->assertEquals(1, $userRecord['UserID']);

		$userRecord = $userRepo->find(1000);
		$this->assertNull($userRecord);
	}

	#[Test]
	public function all(): void
	{
		$userRepo = new UserRepository();
		$userRepo->invoke($this->query, primaryField: 'UserID', table: 'User');

		$userRecords = $userRepo->all();
		$this->assertCount(5, $userRecords);

		$userRecords = $userRepo->all(['Lastname' => 'Doe']);
		$this->assertCount(4, $userRecords);
	}

	#[Test]
	public function search(): void
	{
		$userRepo = new UserRepository();
		$userRepo->invoke($this->query, primaryField: 'UserID', table: 'User');

		$userRecords = $userRepo->search('Lastname', 'D');
		$this->assertCount(4, $userRecords);
	}

	#[Test]
	public function clone(): void
	{
		$userRepo = new UserRepository();
		$userRepo->invoke($this->query, primaryField: 'UserID', table: 'User');

		$newUser = [
			'Username' => 'jasmine.doe@example.com',
			'Firstname' => 'Jasmine',
			'Lastname' => 'Doe',
			'Password' => 'password',
			'Active' => 0
		];
		$userID = 1;
		$newUserRecord = $userRepo->clone($userID, $newUser);
		$this->assertNotEquals($userID, $newUserRecord['UserID']);
		$this->assertEquals($newUser['Username'], $newUserRecord['Username']);

		// Maintain consistency in the database
		$userRepo->delete($newUserRecord['UserID']);
	}

	#[Test]
	public function sum(): void
	{
		$userRepo = new UserRepository();
		$userRepo->invoke($this->query, primaryField: 'UserID', table: 'User');

		$sum = 0;
		$allUserRecords = $userRepo->all();
		foreach ($allUserRecords as $userRecord)
			$sum += $userRecord['UserID'];

		$userRecords = $userRepo->sum('UserID');
		$this->assertCount(1, $userRecords);

		$userRecord = array_pop($userRecords);
		$this->assertEquals($sum, $userRecord['sum']);

		$userRecords = $userRepo->sum('UserID', ['Lastname' => 'Doe']);
		$this->assertNotNull($userRecords);

		$userRecords = $userRepo->sum('UserID', ['Lastname' => 'Doe'], ['Lastname']);
		$this->assertNotNull($userRecords);
	}

	#[Test]
	public function counts(): void
	{
		$userRepo = new UserRepository();
		$userRepo->invoke($this->query, primaryField: 'UserID', table: 'User');

		$userRecords = $userRepo->count();
		$this->assertCount(1, $userRecords);

		$userRecord = array_pop($userRecords);
		$this->assertEquals(5, (int) $userRecord['count']);

		$userRecords = $userRepo->count('UserID', ['Lastname' => 'Doe'], ['Lastname']);
		$this->assertCount(1, $userRecords);

		$userRecord = array_pop($userRecords);
		$this->assertEquals(4, (int) $userRecord['count']);

		$allUserRecords = $userRepo->all();
		$userRecords = $userRepo->count();
		$userRecord = array_pop($userRecords);
		$allUserCount = (int) $userRecord['count'];
		$this->assertEquals(count($allUserRecords), $allUserCount);

		$results = $userRepo->count('Lastname', ['Lastname' => 'Doe']);
		$result = array_pop($results);
		$count = (int) $result['count'];

		$userRecords = $userRepo->all(['Lastname' => 'Doe']);
		$this->assertCount($count, $userRecords);
	}

	#[Test]
	public function avg(): void
	{
		$userRepo = new UserRepository();
		$userRepo->invoke($this->query, primaryField: 'UserID', table: 'User');

		$sum = 0;
		$allUserRecords = $userRepo->all();
		foreach ($allUserRecords as $userRecord)
			$sum += $userRecord['UserID'];

		$userRecords = $userRepo->count();
		$userRecord = array_pop($userRecords);
		$allUserCount = (int) $userRecord['count'];

		$userRecords = $userRepo->avg('UserID');
		$this->assertCount(1, $userRecords);

		$userRecord = array_pop($userRecords);
		$this->assertEquals(round($sum / $allUserCount), round((float) $userRecord['avg']));

		$userRecords = $userRepo->avg('UserID', ['Lastname' => 'Doe'], ['Lastname']);
		$this->assertCount(1, $userRecords);
	}

	#[Test]
	public function min(): void
	{
		$userRepo = new UserRepository();
		$userRepo->invoke($this->query, primaryField: 'UserID', table: 'User');

		$userRecords = $userRepo->min('UserID', ['Lastname' => 'Doe'], ['Lastname']);
		$this->assertCount(1, $userRecords);

		$userRecord = array_pop($userRecords);
		$this->assertEquals(2, $userRecord['min']);
	}

	#[Test]
	public function max(): void
	{
		$userRepo = new UserRepository();
		$userRepo->invoke($this->query, primaryField: 'UserID', table: 'User');

		$userRecords = $userRepo->max('UserID', ['Lastname' => 'Doe'], ['Lastname']);
		$this->assertCount(1, $userRecords);

		$userRecord = array_pop($userRecords);
		$this->assertEquals(5, $userRecord['max']);
	}

	#[Test]
	public function paginate(): void
	{
		$userRepo = new UserRepository();
		$userRepo->invoke($this->query, primaryField: 'UserID', table: 'User');

		$userRecords = $userRepo->paginate(2, 2, ['Lastname' => 'Doe']);
		$this->assertCount(2, $userRecords);
	}

	#[Test]
	public function redactFields(): void
	{
		$userRepo = new UserRepository();
		$userRepo->invoke($this->query, primaryField: 'UserID', table: 'User');
		$userRecord = $userRepo->find(1);
		$this->assertEquals('__REDACTED__', $userRecord['Password']);
	}

	#[Test]
	public function formatFields(): void
	{
		$userRepo = new UserRepository();
		$userRepo->invoke($this->query, primaryField: 'UserID', table: 'User');
		$userRecord = $userRepo->find(1);

		$this->assertIsInt($userRecord['UserID']);
		$this->assertIsString($userRecord['Lastname']);
		$this->assertIsString($userRecord['Username']);
		$this->assertIsString($userRecord['Password']);
		$this->assertIsBool($userRecord['Active']);
	}

	#[Test]
	public function selectFields(): void
	{
		$userRepo = new UserRepository();
		$userRepo->invoke($this->query, primaryField: 'UserID', table: 'User');
		$userRecord = $userRepo->find(1);

		$this->assertArrayHasKey('UserID', $userRecord);
		$this->assertArrayHasKey('Username', $userRecord);
		$this->assertArrayHasKey('Lastname', $userRecord);
		$this->assertArrayHasKey('Password', $userRecord);
		$this->assertArrayHasKey('Active', $userRecord);
		$this->assertArrayHasKey('Updated', $userRecord);

		$this->assertArrayNotHasKey('Firstname', $userRecord);
		$this->assertArrayNotHasKey('Created', $userRecord);
	}
}
