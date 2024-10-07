<?php

namespace Projom\Storage\SQL\Util;

enum LogicalOperator: string
{
	case AND = 'AND';
	case OR = 'OR';

	public static function values(): array
	{
		return array_map(fn($case) => $case->value, static::cases());
	}
}
