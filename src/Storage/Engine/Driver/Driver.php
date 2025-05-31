<?php

declare(strict_types=1);

namespace Projom\Storage\Engine\Driver;

enum Driver: string
{
	case MySQL = 'mysql';
	case CSV = 'csv';

	public static function values(): array
	{
		return array_map(fn($case) => $case->value, static::cases());
	}
}
