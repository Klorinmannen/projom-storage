<?php

declare(strict_types=1);

namespace Projom\Tests\Integration;

use Projom\Storage\Query\RecordInterface;

class UserRecord implements RecordInterface
{
	public function __construct(private array $record = []) {}

	public static function fromRecord(array $record): static
	{
		return new UserRecord($record);
	}
}
