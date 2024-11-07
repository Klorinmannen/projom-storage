<?php

declare(strict_types=1);

namespace Projom\Tests\EndToEnd;

use Projom\Storage\Model\MySQLModel;
use Projom\Storage\Query\RecordInterface;

class User extends MySQLModel implements RecordInterface
{
	const PRIMARY_FIELD = 'UserID';
	const REDACTED_FIELDS = ['Password'];
	const FORMAT_FIELDS = [
		'UserID' => 'int',
		'Firstname' => 'string',
		'Lastname' => 'string',
		'Username ' => 'string',
		'Password' => 'string',
		'Active' => 'bool',
		'Created' => 'date',
		'Updated' => 'datetime'
	];

	private int $userID;
	private string $username;
	private string $password;
	private null|string $firstname;
	private null|string $lastname;
	private bool $active;
	private string $created;
	private string $updated;

	public function __construct(array $record)
	{
		$this->userID = (int) $record['UserID'];
		$this->username = $record['Username'];
		$this->password = $record['Password'];
		$this->firstname = $record['Firstname'];
		$this->lastname = $record['Lastname'];
		$this->active = (bool) $record['Active'];
		$this->created = $record['Created'];
		$this->updated = $record['Updated'];
	}

	public static function createFromRecord(array $record): object
	{
		return new User($record);
	}
}
