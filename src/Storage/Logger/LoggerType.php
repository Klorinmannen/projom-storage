<?php

declare(strict_types=1);

namespace Projom\Storage\Logger;

use Stringable;

enum LoggerType implements Stringable
{
	case FILE;
	case ERROR_LOG;
	case LOG_STORE;

	public function __toString(): string
	{
		return $this->name;
	}
}
