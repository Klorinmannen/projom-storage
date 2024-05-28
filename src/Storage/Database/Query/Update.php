<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Query;

/**
 * DTO for an update query.
 */
class Update
{
	public function __construct(
		public array $collections,
		public array $fieldsWithValues,
		public array $filters = []
	) {
	}
}
