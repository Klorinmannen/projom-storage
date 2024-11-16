<?php

declare(strict_types=1);

namespace Projom\Storage\Logger;

enum LoggerType: string
{
	case FILE = 'FILE';
	case ERROR_LOG = 'ERROR_LOG';
	case LOG_STORE = 'LOG_STORE';

	public static function values(): array
	{
		return array_map(fn($case) => $case->value, static::cases());
	}
}
