<?php

declare(strict_types=1);

namespace Projom\Storage\SQL\Util;

enum Join: string
{
	case INNER = 'INNER JOIN';
	case LEFT = 'LEFT JOIN';
	case RIGHT = 'RIGHT JOIN';
	case FULL = 'FULL JOIN';
	case CROSS = 'CROSS JOIN';
	case STRAIGHT = 'STRAIGHT JOIN';
	case OUTER = 'OUTER JOIN';
	case NATURAL = 'NATURAL JOIN';

	public static function values(): array
	{
		return array_map(fn($case) => $case->value, static::cases());
	}
}
