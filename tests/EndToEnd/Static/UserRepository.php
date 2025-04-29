<?php

declare(strict_types=1);

namespace Projom\Tests\EndToEnd\Static;

use Projom\Storage\Static\MySQL\Repository;

class UserRepository
{
	use Repository;

	public static function primaryField(): string
	{
		return 'UserID';
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
