<?php

declare(strict_types=1);

namespace Projom\Storage\Database;

use Projom\Storage\Database\Query\Constraint;

Interface QueryInterface 
{
	public function select(Constraint ...$constraints): mixed;
	public function fetch(string $field, mixed $value): mixed;
	public function eq(array ...$constraints): mixed;
	public function ne(array ...$constraints): mixed;
}
