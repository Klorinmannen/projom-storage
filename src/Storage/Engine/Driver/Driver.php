<?php

declare(strict_types=1);

namespace Projom\Storage\Engine\Driver;

use Stringable;

enum Driver: string implements Stringable
{
	case MySQL = 'mysql';

	public static function values(): array
	{
		return array_map(fn($case) => $case->value, static::cases());
	}

	public function __toString(): string
	{
		return $this->name;
	}
}
