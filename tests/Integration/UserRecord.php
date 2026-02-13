<?php

declare(strict_types=1);

namespace JRF\Tests\Integration;

use JRF\Storage\Database\Util\RecordInterface;

class UserRecord implements RecordInterface
{
	public function __construct(private array $record = []) {}

	public static function fromRecord(array $record): static
	{
		return new UserRecord($record);
	}
}
