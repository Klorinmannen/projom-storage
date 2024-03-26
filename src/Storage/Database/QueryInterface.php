<?php

declare(strict_types=1);

namespace Projom\Storage\Database;

use Projom\Storage\Database\Query;
use Projom\Storage\Database\Query\Constraint;
use Projom\Storage\Database\Query\Field;

Interface QueryInterface 
{
	public function select(Field $field, Constraint ...$constraints): mixed;
	public function fetch(string $field, mixed $value): mixed;
	public function field(string ...$fields): Query;
}
