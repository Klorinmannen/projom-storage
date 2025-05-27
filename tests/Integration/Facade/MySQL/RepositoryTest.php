<?php

declare(strict_types=1);

namespace Projom\tests\Integration\Facade\MySQL;

include_once __DIR__ . '/../UserRepository.php';

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Engine as EngineObject;
use Projom\Storage\Facade\Engine;
use Projom\Tests\Integration\Facade\UserRepository;

class RepositoryTest extends TestCase
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

	#[Test]
	public function crud()
	{
		// Create a new user
		$newUser = [
			'Username' => 'anya.doe@example.com',
			'Firstname' => 'Anya',
			'Lastname' => 'Doe',
			'Password' => 'password'
		];
		$userID = UserRepository::create($newUser);

		// Check if the new user was created
		$userRecord = UserRepository::get('UserID', $userID);
		$this->assertEquals($newUser['Username'], $userRecord['Username']);

		// Update the new user
		$smith = 'Smith';
		UserRepository::update($userID, ['Lastname' => $smith]);

		// Check if the new user was updated
		$userRecord = UserRepository::get('Lastname', $smith);
		$this->assertNotNull($userRecord);

		// Delete the new user
		UserRepository::delete($userID);

		// Check if the new user was deleted
		$userRecord = UserRepository::find($userID);
		$this->assertNull($userRecord);
	}

	#[Test]
	public function find(): void
	{
		$userRecord = UserRepository::find(1);
		$this->assertEquals(1, $userRecord['UserID']);

		$userRecord = UserRepository::find(1000);
		$this->assertNull($userRecord);
	}

	#[Test]
	public function all(): void
	{
		$userRecords = UserRepository::all();
		$this->assertCount(5, $userRecords);

		$userRecords = UserRepository::all(['Lastname' => 'Doe']);
		$this->assertCount(4, $userRecords);
	}

	#[Test]
	public function search(): void
	{
		$userRecords = UserRepository::search('Lastname', 'D');
		$this->assertCount(4, $userRecords);
	}

	#[Test]
	public function clone(): void
	{
		$newUser = [
			'Username' => 'jasmine.doe@example.com',
			'Firstname' => 'Jasmine',
			'Lastname' => 'Doe',
			'Password' => 'password',
			'Active' => 0
		];
		$userID = 1;
		$newUserRecord = UserRepository::clone($userID, $newUser);
		$this->assertNotEquals($userID, $newUserRecord['UserID']);
		$this->assertEquals($newUser['Username'], $newUserRecord['Username']);

		// Maintain consistency in the database
		UserRepository::delete($newUserRecord['UserID']);
	}

	#[Test]
	public function sum(): void
	{
		$sum = 0;
		$allUserRecords = UserRepository::all();
		foreach ($allUserRecords as $userRecord)
			$sum += $userRecord['UserID'];

		$userRecords = UserRepository::sum('UserID');
		$this->assertCount(1, $userRecords);

		$userRecord = array_pop($userRecords);
		$this->assertEquals($sum, $userRecord['sum']);

		$userRecords = UserRepository::sum('UserID', ['Lastname' => 'Doe']);
		$this->assertNotNull($userRecords);

		$userRecords = UserRepository::sum('UserID', ['Lastname' => 'Doe'], ['Lastname']);
		$this->assertNotNull($userRecords);
	}

	#[Test]
	public function counts(): void
	{
		$userRecords = UserRepository::count();
		$this->assertCount(1, $userRecords);

		$userRecord = array_pop($userRecords);
		$this->assertEquals(5, (int) $userRecord['count']);

		$userRecords = UserRepository::count('UserID', ['Lastname' => 'Doe'], ['Lastname']);
		$this->assertCount(1, $userRecords);

		$userRecord = array_pop($userRecords);
		$this->assertEquals(4, (int) $userRecord['count']);

		$allUserRecords = UserRepository::all();
		$userRecords = UserRepository::count();
		$userRecord = array_pop($userRecords);
		$allUserCount = (int) $userRecord['count'];
		$this->assertEquals(count($allUserRecords), $allUserCount);

		$results = UserRepository::count('Lastname', ['Lastname' => 'Doe']);
		$result = array_pop($results);
		$count = (int) $result['count'];

		$userRecords = UserRepository::all(['Lastname' => 'Doe']);
		$this->assertCount($count, $userRecords);
	}

	#[Test]
	public function avg(): void
	{
		$sum = 0;
		$allUserRecords = UserRepository::all();
		foreach ($allUserRecords as $userRecord)
			$sum += $userRecord['UserID'];

		$userRecords = UserRepository::count();
		$userRecord = array_pop($userRecords);
		$allUserCount = (int) $userRecord['count'];

		$userRecords = UserRepository::avg('UserID');
		$this->assertCount(1, $userRecords);

		$userRecord = array_pop($userRecords);
		$this->assertEquals(round($sum / $allUserCount), round((float) $userRecord['avg']));

		$userRecords = UserRepository::avg('UserID', ['Lastname' => 'Doe'], ['Lastname']);
		$this->assertCount(1, $userRecords);
	}

	#[Test]
	public function min(): void
	{
		$userRecords = UserRepository::min('UserID', ['Lastname' => 'Doe'], ['Lastname']);
		$this->assertCount(1, $userRecords);

		$userRecord = array_pop($userRecords);
		$this->assertEquals(2, $userRecord['min']);
	}

	#[Test]
	public function max(): void
	{
		$userRecords = UserRepository::max('UserID', ['Lastname' => 'Doe'], ['Lastname']);
		$this->assertCount(1, $userRecords);

		$userRecord = array_pop($userRecords);
		$this->assertEquals(5, $userRecord['max']);
	}

	#[Test]
	public function paginate(): void
	{
		$userRecords = UserRepository::paginate(2, 2, ['Lastname' => 'Doe']);
		$this->assertCount(2, $userRecords);
	}

	#[Test]
	public function redactFields(): void
	{
		$userRecord = UserRepository::find(1);
		$this->assertEquals('__REDACTED__', $userRecord['Password']);
	}

	#[Test]
	public function formatFields(): void
	{
		$userRecord = UserRepository::find(1);

		$this->assertIsInt($userRecord['UserID']);
		$this->assertIsString($userRecord['Lastname']);
		$this->assertIsString($userRecord['Username']);
		$this->assertIsString($userRecord['Password']);
		$this->assertIsBool($userRecord['Active']);
	}

	#[Test]
	public function selectFields(): void
	{
		$userRecord = UserRepository::find(1);

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
