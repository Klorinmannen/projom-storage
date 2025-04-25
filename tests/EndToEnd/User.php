<?php

declare(strict_types=1);

namespace Projom\Tests\EndToEnd;

use Projom\Storage\MySQL\Repository;
use Projom\Storage\Query\RecordInterface;

class User implements RecordInterface
{
	use Repository;
	
	private array $data = [];

	public function __construct(array $record = [])
	{
		$this->data = $record;
	}

	public function primaryField(): string
	{
		return 'UserID';
	}

	public function redactFields(): array
	{
		return [
			'Password'
		];
	}

	public function formatFields(): array
	{
		return [
			'UserID' => 'int',
			'Firstname' => 'string',
			'Lastname' => 'string',
			'Username ' => 'string',
			'Password' => 'string',
			'Active' => 'bool',
			'Created' => 'date',
			'Updated' => 'datetime'
		];
	}

	public static function createFromRecord(array $record): object
	{
		return new User($record);
	}
}
