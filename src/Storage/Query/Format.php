<?php

declare(strict_types=1);

namespace Projom\Storage\Query;

enum Format: string
{
	case ARRAY = 'array';
	case STD_CLASS = 'std_class';
	case CUSTOM_OBJECT = 'custom_object';

	public static function values(): array
	{
		return array_map(fn($case) => $case->value, static::cases());
	}
}
