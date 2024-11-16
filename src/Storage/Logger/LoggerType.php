<?php

declare(strict_types=1);

namespace Projom\Storage\Logger;

enum LoggerType: string
{
	case FILE = 'file';
	case ERROR_LOG = 'error_log';
	case LOG_STORE = 'log_store';

	public static function values(): array
	{
		return array_map(fn($case) => $case->value, static::cases());
	}
}
