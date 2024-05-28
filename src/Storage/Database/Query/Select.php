<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Query;

/**
 * DTO for a select query.
 */
class Select
{
	public function __construct(
		public array $collections,
		public array $fields,
		public array $filters = [],
		public array $order = [],
		public int|string $limit = ''
	) {
	}
}
