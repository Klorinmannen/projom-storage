<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Query;

enum AggregateFunction: string
{
	case COUNT = 'COUNT';
	case MIN = 'MIN';
	case MAX = 'MAX';
	case AVG = 'AVG';
	case SUM = 'SUM';

	public static function values(): array
	{
		return array_map(fn ($case) => $case->value, static::cases());
	}

	public static function build(AggregateFunction $function, string $field): string
	{
		return "{$function->value}({$field})";
	}
}
