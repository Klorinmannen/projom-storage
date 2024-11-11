<?php

declare(strict_types=1);

namespace Projom\tests\EndToEnd;

include_once __DIR__ . '/User.php';

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Engine;
use Projom\Tests\EndToEnd\User;

class ModelTest extends TestCase
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
	public function test()
	{
		$user = new User();

		$newUser = [
			'Username' => 'anya.doe@example.com',
			'Firstname' => 'Anya',
			'Lastname' => 'Doe',
			'Password' => 'password'
		];
		$userID = $user->create($newUser);

		$userRecord = $user->find($userID);
		$this->assertNotNull($userRecord);
		$this->assertEquals($newUser['Username'], $userRecord['Username']);

		$user->update($userID, ['Lastname' => 'Smith']);
		$userRecord = $user->find($userID);
		$this->assertEquals('Smith', $userRecord['Lastname']);

		$userRecord = $user->get('Lastname', 'Smith');
		$this->assertNotNull($userRecord);

		$newUser = [
			'Username' => 'jasmine.doe@example.com',
			'Firstname' => 'Jasmine',
			'Lastname' => 'Doe',
			'Password' => 'password',
			'Active' => 0
		];
		$userRecord = $user->clone($userID, $newUser);
		$this->assertNotEquals($userID, $userRecord['UserID']);
		$this->assertEquals($newUser['Username'], $userRecord['Username']);

		$user->delete($userRecord['UserID']);
		$userRecord = $user->find($userRecord['UserID']);
		$this->assertNull($userRecord);

		$results = $user->count('Lastname', ['Lastname' => 'Doe']);
		$result = array_pop($results);
		$count = (int) $result['count'];

		$userRecords = $user->all(['Lastname' => 'Doe']);
		$this->assertNotNull($userRecords);
		$this->assertCount($count, $userRecords);

		$userRecords = $user->search('Lastname', 'D');
		$this->assertNotNull($userRecords);

		$allUserRecords = $user->all();
		$this->assertNotNull($userRecords);

		$sum = 0;
		foreach ($allUserRecords as $userRecord)
			$sum += $userRecord['UserID'];

		$userRecords = $user->sum('UserID');
		$this->assertNotNull($userRecords);
		$this->assertCount(1, $userRecords);
		$userRecord = array_pop($userRecords);
		$this->assertEquals($sum, $userRecord['sum']);

		$userRecords = $user->sum('UserID', ['Lastname' => 'Doe']);
		$this->assertNotNull($userRecords);

		$userRecords = $user->sum('UserID', ['Lastname' => 'Doe'], ['Lastname']);
		$this->assertNotNull($userRecords);

		$userRecords = $user->count('UserID', ['Lastname' => 'Doe'], ['Lastname']);
		$this->assertNotNull($userRecords);
		$this->assertCount(1, $userRecords);
		$userRecord = array_pop($userRecords);
		$this->assertEquals(4, (int) $userRecord['count']);

		$userRecords = $user->count();
		$userRecord = array_pop($userRecords);
		$allUserCount = (int) $userRecord['count'];
		$this->assertEquals(count($allUserRecords), $allUserCount);

		$userRecords = $user->avg('UserID');
		$this->assertNotNull($userRecords);
		$this->assertCount(1, $userRecords);
		$userRecord = array_pop($userRecords);
		$this->assertEquals(round($sum / $allUserCount), round((float) $userRecord['avg']));

		$userRecords = $user->avg('UserID', ['Lastname' => 'Doe'], ['Lastname']);
		$this->assertNotNull($userRecords);
		$this->assertCount(1, $userRecords);

		$userRecords = $user->min('UserID', ['Lastname' => 'Doe'], ['Lastname']);
		$this->assertNotNull($userRecords);
		$this->assertCount(1, $userRecords);
		$userRecord = array_pop($userRecords);
		$this->assertEquals(2, $userRecord['min']);

		$userRecords = $user->max('UserID', ['Lastname' => 'Doe'], ['Lastname']);
		$this->assertNotNull($userRecords);
		$this->assertCount(1, $userRecords);
		$userRecord = array_pop($userRecords);
		$this->assertEquals(5, $userRecord['max']);

		$userRecords = $user->paginate(2, 2, ['Lastname' => 'Doe']);
		$this->assertNotNull($userRecords);
		$this->assertCount(2, $userRecords);

		$user->delete($userID);
	}
}
