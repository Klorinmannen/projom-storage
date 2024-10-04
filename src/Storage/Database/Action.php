<?php

declare(strict_types=1);

namespace Projom\Storage\Database;

enum Action
{
	case SELECT;
	case INSERT;
	case UPDATE;
	case DELETE;
	case EXECUTE;
	case QUERY;
	case START_TRANSACTION;
	case END_TRANSACTION;
	case REVERT_TRANSACTION;
}
