<?php

declare(strict_types=1);

namespace Projom\Storage\Database;

Interface QueryInterface
{
	public function select(string $column, mixed $value): mixed;
}
