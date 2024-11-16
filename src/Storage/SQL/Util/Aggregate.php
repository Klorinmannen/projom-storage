<?php

declare(strict_types=1);

namespace Projom\Storage\SQL\Util;

use Stringable;

enum Aggregate: string implements Stringable
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

	public function __toString(): string
	{
		return $this->name;
	}
}
