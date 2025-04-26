<?php

declare(strict_types=1);

namespace Projom\tests\EndToEnd\Static;

include_once __DIR__ . '/User.php';

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Static\Engine;
use Projom\Tests\EndToEnd\Static\User;

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
					'database' => 'EndToEnd'
				]
			]
		];

		Engine::start();
		Engine::loadDriver($config);
	}

	#[Test]
	public function crud()
	{
		$newUser = [
			'Username' => 'anya.doe@example.com',
			'Firstname' => 'Anya',
			'Lastname' => 'Doe',
			'Password' => 'password'
		];
		$userID = User::create($newUser);

		$userRecord = User::find($userID);
		$this->assertNotNull($userRecord);
		$this->assertEquals($newUser['Username'], $userRecord['Username']);

		User::update($userID, ['Lastname' => 'Smith']);
		$userRecord = User::find($userID);
		$this->assertEquals('Smith', $userRecord['Lastname']);

		$userRecord = User::get('Lastname', 'Smith');
		$this->assertNotNull($userRecord);

		$newUser = [
			'Username' => 'jasmine.doe@example.com',
			'Firstname' => 'Jasmine',
			'Lastname' => 'Doe',
			'Password' => 'password',
			'Active' => 0
		];
		$userRecord = User::clone($userID, $newUser);
		$this->assertNotEquals($userID, $userRecord['UserID']);
		$this->assertEquals($newUser['Username'], $userRecord['Username']);

		User::delete($userRecord['UserID']);
		$userRecord = User::find($userRecord['UserID']);
		$this->assertNull($userRecord);

		$userRecords = User::search('Lastname', 'D');
		$this->assertNotNull($userRecords);

		User::delete($userID);
	}

	#[Test]
	public function sum(): void
	{
		$sum = 0;
		$allUserRecords = User::all();
		foreach ($allUserRecords as $userRecord)
			$sum += $userRecord['UserID'];

		$userRecords = User::sum('UserID');
		$this->assertNotNull($userRecords);
		$this->assertCount(1, $userRecords);
		$userRecord = array_pop($userRecords);
		$this->assertEquals($sum, $userRecord['sum']);

		$userRecords = User::sum('UserID', ['Lastname' => 'Doe']);
		$this->assertNotNull($userRecords);

		$userRecords = User::sum('UserID', ['Lastname' => 'Doe'], ['Lastname']);
		$this->assertNotNull($userRecords);
	}

	#[Test]
	public function counts(): void
	{
		$userRecords = User::count();
		$this->assertNotNull($userRecords);
		$this->assertCount(1, $userRecords);
		$userRecord = array_pop($userRecords);
		$this->assertEquals(5, (int) $userRecord['count']);

		$userRecords = User::count('UserID', ['Lastname' => 'Doe'], ['Lastname']);
		$this->assertNotNull($userRecords);
		$this->assertCount(1, $userRecords);
		$userRecord = array_pop($userRecords);
		$this->assertEquals(4, (int) $userRecord['count']);

		$allUserRecords = User::all();
		$userRecords = User::count();
		$userRecord = array_pop($userRecords);
		$allUserCount = (int) $userRecord['count'];
		$this->assertEquals(count($allUserRecords), $allUserCount);

		$results = User::count('Lastname', ['Lastname' => 'Doe']);
		$result = array_pop($results);
		$count = (int) $result['count'];

		$userRecords = User::all(['Lastname' => 'Doe']);
		$this->assertNotNull($userRecords);
		$this->assertCount($count, $userRecords);
	}

	#[Test]
	public function avg(): void
	{
		$sum = 0;
		$allUserRecords = User::all();
		foreach ($allUserRecords as $userRecord)
			$sum += $userRecord['UserID'];

		$userRecords = User::count();
		$userRecord = array_pop($userRecords);
		$allUserCount = (int) $userRecord['count'];

		$userRecords = User::avg('UserID');
		$this->assertNotNull($userRecords);
		$this->assertCount(1, $userRecords);
		$userRecord = array_pop($userRecords);
		$this->assertEquals(round($sum / $allUserCount), round((float) $userRecord['avg']));

		$userRecords = User::avg('UserID', ['Lastname' => 'Doe'], ['Lastname']);
		$this->assertNotNull($userRecords);
		$this->assertCount(1, $userRecords);
	}

	#[Test]
	public function min(): void
	{
		$userRecords = User::min('UserID', ['Lastname' => 'Doe'], ['Lastname']);
		$this->assertNotNull($userRecords);
		$this->assertCount(1, $userRecords);
		$userRecord = array_pop($userRecords);
		$this->assertEquals(2, $userRecord['min']);
	}

	#[Test]
	public function max(): void
	{
		$userRecords = User::max('UserID', ['Lastname' => 'Doe'], ['Lastname']);
		$this->assertNotNull($userRecords);
		$this->assertCount(1, $userRecords);
		$userRecord = array_pop($userRecords);
		$this->assertEquals(5, $userRecord['max']);
	}

	#[Test]
	public function paginate(): void
	{
		$userRecords = User::paginate(2, 2, ['Lastname' => 'Doe']);
		$this->assertNotNull($userRecords);
		$this->assertCount(2, $userRecords);
	}
}
