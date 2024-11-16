<?php

declare(strict_types=1);

namespace Projom\Storage\SQL\Util;

use Stringable;

enum Join: string implements Stringable
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

	public function __toString(): string
	{
		return $this->name;
	}
}
