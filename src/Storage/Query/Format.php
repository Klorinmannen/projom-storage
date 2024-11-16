<?php

declare(strict_types=1);

namespace Projom\Storage\Query;

enum Format: string
{
	case ARRAY = 'ARRAY';
	case STD_CLASS = 'STD_CLASS';
	case CUSTOM_OBJECT = 'CUSTOM_OBJECT';

	public static function values(): array
	{
		return array_map(fn($case) => $case->value, static::cases());
	}
}
