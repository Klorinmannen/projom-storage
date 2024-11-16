<?php

namespace Projom\Storage\SQL\Util;

use Stringable;

enum LogicalOperator: string implements Stringable
{
	case AND = 'AND';
	case OR = 'OR';

	public static function values(): array
	{
		return array_map(fn($case) => $case->value, static::cases());
	}

	public function __toString(): string
	{
		return $this->name;
	}
}
