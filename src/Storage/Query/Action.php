<?php

declare(strict_types=1);

namespace Projom\Storage\Query;

enum Action: string
{
	case CHANGE_CONNECTION = 'CHANGE_CONNECTION';
	case SELECT = 'SELECT';
	case INSERT = 'INSERT';
	case UPDATE = 'UPDATE';
	case DELETE = 'DELETE';
	case EXECUTE = 'EXECUTE';
	case QUERY = 'QUERY';
	case START_TRANSACTION = 'START_TRANSACTION';
	case END_TRANSACTION = 'END_TRANSACTION';
	case REVERT_TRANSACTION = 'REVERT_TRANSACTION';

	public static function values(): array
	{
		return array_map(fn($case) => $case->value, static::cases());
	}
}
