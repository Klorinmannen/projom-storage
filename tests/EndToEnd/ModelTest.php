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

		$user = $user->find($userID);
		$this->assertNotNull($user);
		$this->assertEquals($newUser['Username'], $user['Username']);

		$user->update($userID, ['Lastname' => 'Smith']);
		$user = $user->find($userID);
		$this->assertEquals('Smith', $user['Lastname']);

		$user = $user->get('Lastname', 'Smith');
		$this->assertNotNull($user);

		$newUser = [
			'Username' => 'jasmine.doe@example.com',
			'Firstname' => 'Jasmine',
			'Lastname' => 'Doe',
			'Password' => 'password',
			'Active' => 0
		];
		$cloneUser = $user->clone($userID, $newUser);
		$this->assertNotEquals($userID, $cloneUser['UserID']);
		$this->assertEquals($newUser['Username'], $cloneUser['Username']);

		$user->delete($cloneUser['UserID']);
		$record = $user->find($cloneUser['UserID']);
		$this->assertNull($record);

		$results = $user->count('Lastname', ['Lastname' => 'Doe']);
		$result = array_pop($results);
		$count = (int) $result['count'];

		$user = $user->all(['Lastname' => 'Doe']);
		$this->assertNotNull($user);
		$this->assertCount($count, $user);

		$records = $user->search('Lastname', 'D');
		$this->assertNotNull($records);

		$allUsers = $user->all();
		$this->assertNotNull($allUsers);

		$sum = 0;
		foreach ($allUsers as $user)
			$sum += $user['UserID'];

		$records = $user->sum('UserID');
		$this->assertNotNull($records);
		$this->assertCount(1, $records);
		$record = array_pop($records);
		$this->assertEquals($sum, $record['sum']);

		$records = $user->sum('UserID', ['Lastname' => 'Doe']);
		$this->assertNotNull($records);

		$records = $user->sum('UserID', ['Lastname' => 'Doe'], ['Lastname']);
		$this->assertNotNull($records);

		$records = $user->count('UserID', ['Lastname' => 'Doe'], ['Lastname']);
		$this->assertNotNull($records);
		$this->assertCount(1, $records);
		$record = array_pop($records);
		$this->assertEquals(4, (int) $record['count']);

		$records = $user->count();
		$record = array_pop($records);
		$allCount = (int) $record['count'];
		$this->assertEquals(count($allUsers), $allCount);

		$records = $user->avg('UserID');
		$this->assertNotNull($records);
		$this->assertCount(1, $records);
		$record = array_pop($records);
		$this->assertEquals(round($sum / $allCount), round((float) $record['avg']));

		$records = $user->avg('UserID', ['Lastname' => 'Doe'], ['Lastname']);
		$this->assertNotNull($records);
		$this->assertCount(1, $records);

		$records = $user->min('UserID', ['Lastname' => 'Doe'], ['Lastname']);
		$this->assertNotNull($records);
		$this->assertCount(1, $records);
		$record = array_pop($records);
		$this->assertEquals(2, $record['min']);

		$records = $user->max('UserID', ['Lastname' => 'Doe'], ['Lastname']);
		$this->assertNotNull($records);
		$this->assertCount(1, $records);
		$record = array_pop($records);
		$this->assertEquals(5, $record['max']);

		$records = $user->paginate(2, 2, ['Lastname' => 'Doe']);
		$this->assertNotNull($records);
		$this->assertCount(2, $records);

		$user->delete($userID);
	}
}
