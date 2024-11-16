<?php

declare(strict_types=1);

namespace Projom\Storage\Query;

enum Action: string
{
	case CHANGE_CONNECTION = 'change_connection';
	case SELECT = 'select';
	case INSERT = 'insert';
	case UPDATE = 'update';
	case DELETE = 'delete';
	case EXECUTE = 'execute';
	case QUERY = 'query';
	case START_TRANSACTION = 'start_transaction';
	case END_TRANSACTION = 'end_transaction';
	case REVERT_TRANSACTION = 'revert_transaction';

	public static function values(): array
	{
		return array_map(fn($case) => $case->value, static::cases());
	}
}
