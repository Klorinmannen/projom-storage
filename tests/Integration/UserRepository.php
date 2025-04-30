<?php

declare(strict_types=1);

namespace Projom\Tests\Integration;

use Projom\Storage\MySQL\Repository;

class UserRepository
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

	public static function selectFields(): array
	{
		return [
			'UserID',
			'Username',
			'Lastname',
			'Password',
			'Active',
			'Updated'
		];
	}
}
