<?php

declare(strict_types=1);

namespace Projom\Storage\SQL\Util;

use Stringable;

enum Sort: string implements Stringable
{
	case ASC = 'ASC';
	case DESC = 'DESC';

	public static function values(): array
	{
		return array_map(fn($case) => $case->value, static::cases());
	}

	public function __toString(): string
	{
		return $this->name;
	}
}
