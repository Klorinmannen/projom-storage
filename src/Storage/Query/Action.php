<?php

declare(strict_types=1);

namespace Projom\Storage\Query;

use Stringable;

enum Action implements Stringable
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

	public function __toString(): string
	{
		return $this->name;
	}
}
