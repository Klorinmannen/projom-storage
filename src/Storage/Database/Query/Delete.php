<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Query;

/**
 * DTO for a delete query.
 */
class Delete
{
	public function __construct(
		public array $collections,
		public array $filters
	) {}
}
