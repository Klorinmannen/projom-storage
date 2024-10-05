<?php

declare(strict_types=1);

namespace Projom\Storage\Database\MySQL;

enum Sort: string
{
	case ASC = 'ASC';
	case DESC = 'DESC';

	public static function values(): array
	{
		return array_map(fn($case) => $case->value, static::cases());
	}
}
