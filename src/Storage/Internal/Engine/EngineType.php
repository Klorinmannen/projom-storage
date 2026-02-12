<?php

declare(strict_types=1);

namespace JRF\Storage\Internal\Engine;

enum EngineType: string
{
	case MySQL = 'mysql';

	public static function values(): array
	{
		return array_map(fn($case) => $case->value, static::cases());
	}
}
