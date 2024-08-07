<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Query;

enum Action
{
	case SELECT;
	case INSERT;
	case UPDATE;
	case DELETE;
	case EXECUTE;
	case QUERY;
	case COUNT;
	case SUM;
	case AVG;
	case MIN;
	case MAX;
	case START_TRANSACTION;
	case END_TRANSACTION;
	case REVERT_TRANSACTION;
}
