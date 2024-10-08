<?php

declare(strict_types=1);

namespace Projom\Storage\SQL\Util;

enum Aggregate: string
{
	case COUNT = 'COUNT';
	case MIN = 'MIN';
	case MAX = 'MAX';
	case AVG = 'AVG';
	case SUM = 'SUM';

	public static function values(): array
	{
		return array_map(fn($case) => $case->value, static::cases());
	}

	public function buildSQL(string $field, string $alias = ''): string
	{
		$function = "{$this->value}({$field})";

		if ($alias)
			return "$function AS $alias";

		return $function;
	}
}
