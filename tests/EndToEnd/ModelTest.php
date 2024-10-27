<?php

declare(strict_types=1);

namespace Projom\tests\EndToEnd;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Engine;
use Projom\Storage\MySQL\Model;

class User extends Model
{
	const PRIMARY_FIELD = 'UserID';
}

class ModelTest extends TestCase
{
	public function setUp(): void
	{
		$config = [
			'driver' => 'mysql',
			'username' => 'projom',
			'password' => 'projom',
			'host' => 'localhost',
			'port' => 3306,
			'database' => 'EndToEnd'
		];

		Engine::start();
		Engine::loadDriver($config);
	}

	#[Test]
	public function test()
	{
		$newUser = [
			'Username' => 'anya.doe@example.com',
			'Firstname' => 'Anya',
			'Lastname' => 'Doe',
			'Password' => 'password'
		];
		$userID = User::create($newUser);

		$user = User::find($userID);
		$this->assertNotNull($user);
		$this->assertEquals($newUser['Username'], $user['Username']);

		User::update($userID, ['Lastname' => 'Smith']);
		$user = User::find($userID);
		$this->assertEquals('Smith', $user['Lastname']);

		$user = User::get('Lastname', 'Smith');
		$this->assertNotNull($user);

		$newUser = [
			'Username' => 'jasmine.doe@example.com',
			'Firstname' => 'Jasmine',
			'Lastname' => 'Doe',
			'Password' => 'password',
			'Active' => 0
		];
		$cloneUser = User::clone($userID, $newUser);
		$this->assertNotEquals($userID, $cloneUser['UserID']);
		$this->assertEquals($newUser['Username'], $cloneUser['Username']);

		User::delete($cloneUser['UserID']);
		$record = User::find($cloneUser['UserID']);
		$this->assertNull($record);

		$results = User::count('Lastname', ['Lastname' => 'Doe']);
		$result = array_pop($results);
		$count = (int) $result['count'];

		$user = User::all(['Lastname' => 'Doe']);
		$this->assertNotNull($user);
		$this->assertCount($count, $user);

		$records = User::search('Lastname', 'D');
		$this->assertNotNull($records);

		$allUsers = User::all();
		$this->assertNotNull($allUsers);	

		$sum = 0;
		foreach ($allUsers as $user)
			$sum += $user['UserID'];

		$records = User::sum('UserID');
		$this->assertNotNull($records);
		$this->assertCount(1, $records);
		$record = array_pop($records);
		$this->assertEquals($sum, $record['sum']);

		$records = User::sum('UserID', ['Lastname' => 'Doe']);
		$this->assertNotNull($records);

		$records = User::sum('UserID', ['Lastname' => 'Doe'], ['Lastname']);
		$this->assertNotNull($records);

		$records = User::count();
		$record = array_pop($records);
		$allCount = (int) $record['count'];
		$records = User::avg('UserID');
		$this->assertNotNull($records);
		$this->assertCount(1, $records);
		$record = array_pop($records);
		$this->assertEquals(round($sum / $allCount), round((float) $record['avg']));

		$records = User::min('UserID');
		$this->assertNotNull($records);
		$this->assertCount(1, $records);
		$record = array_pop($records);
		$this->assertEquals(1, $record['min']);

		$records = User::max('UserID');
		$this->assertNotNull($records);
		$this->assertCount(1, $records);

		$records = User::paginate(2, 2, ['Lastname' => 'Doe']);
		$this->assertNotNull($records);
		$this->assertCount(2, $records);

		User::delete($userID);
	}
}