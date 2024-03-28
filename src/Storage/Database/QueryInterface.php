<?php

declare(strict_types=1);

namespace Projom\Storage\Database;

use Projom\Storage\Database\Query;
use Projom\Storage\Database\Query\Filter;
use Projom\Storage\Database\Query\Field;

interface QueryInterface 
{
	public function select(Field $field, Filter ...$filters): mixed;
	public function fetch(string $field, mixed $value): mixed;
	public function field(string ...$fields): Query;
}
