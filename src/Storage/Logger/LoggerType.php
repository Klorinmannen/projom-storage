<?php

declare(strict_types=1);

namespace Projom\Storage\Logger;

enum LoggerType
{
	case FILE;
	case ERROR_LOG;
	case LOG_STORE;
}
