<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Query;

/**
 * DTO for a query.
 */
class QueryObject
{
	public function __construct(
		public array $collections,
		public array $fields = [],
		public array $fieldsWithValues = [],
		public array $joins = [],
		public array $filters = [],
		public array $sorts = [],
		public array $groups = [],
		public int|string $limit = ''
	) {
	}
}
