<?php

declare(strict_types=1);

namespace Projom\Storage\Query;

enum Action
{
	case CHANGE_CONNECTION;
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
