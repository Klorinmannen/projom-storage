<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Query;

/**
 * DTO for an insert query.
 */
class Insert
{
	public function __construct(
		public array $collections,
		public array $fieldsWithValues
	) {
	}
}
