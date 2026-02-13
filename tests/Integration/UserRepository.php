<?php

declare(strict_types=1);

namespace JRF\Tests\Integration;

use JRF\Storage\Database\Repository;

class UserRepository
{
	use Repository;

	private array $data = [];

	public function __construct(array $record = [])
	{
		$this->data = $record;
	}

	public static function redactFields(): array
	{
		return [
			'Password'
		];
	}

	public static function formatFields(): array
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
