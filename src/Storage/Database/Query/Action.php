<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Query;

enum Action
{
	case QUERY;
	case EXECUTE;
	case COUNT;
	case SUM;
	case AVG;
	case MIN;
	case MAX;
	case START_TRANSACTION;
	case END_TRANSACTION;
	case REVERT_TRANSACTION;
}
