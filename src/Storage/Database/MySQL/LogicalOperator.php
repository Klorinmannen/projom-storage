<?php

namespace Projom\Storage\Database\MySQL;

enum LogicalOperator: string
{
	case AND = 'AND';
	case OR = 'OR';

	public static function values(): array
	{
		return array_map(fn($case) => $case->value, static::cases());
	}
}
