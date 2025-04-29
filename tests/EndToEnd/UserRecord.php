<?php

declare(strict_types=1);

namespace Projom\Tests\EndToEnd;

use Projom\Storage\Query\RecordInterface;

class UserRecord implements RecordInterface
{
	public function __construct(private array $record = []) {}

	public static function createFromRecord(array $record): object
	{
		return new UserRepository($record);
	}
}
