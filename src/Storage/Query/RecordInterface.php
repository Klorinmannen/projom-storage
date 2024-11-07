<?php

declare(strict_types=1);

namespace Projom\Storage\Query;

interface RecordInterface
{
	/**
	 * Creates a new object from a [field => value] associative array.
	 */
	public static function createFromRecord(array $record): object;
}
