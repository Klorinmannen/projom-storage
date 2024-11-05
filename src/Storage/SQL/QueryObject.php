<?php

declare(strict_types=1);

namespace Projom\Storage\SQL;

use Stringable;

/**
 * DTO for a sql query.
 */
class QueryObject implements Stringable
{
	public function __construct(
		public array $collections,
		public array $fields = [],
		public array $fieldsWithValues = [],
		public array $joins = [],
		public array $filters = [],
		public array $sorts = [],
		public array $groups = [],
		public null|int $limit = null,
		public null|int $offset = null,
		public array $formatting = [],
	) {}

	public function __toString()
	{
		return json_encode($this);
	}
}
