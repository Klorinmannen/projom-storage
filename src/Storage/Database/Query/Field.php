<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Query;

use Projom\Storage\Database\Query\Util;

abstract class Field
{
	protected array $fields = [];

	protected function prepare(array $fields): array
	{
		$fields = Util::cleanList($fields);

		if (!$fields)
			return [];

		if (count($fields) > 1)
			return $fields;

		$fieldString = array_shift($fields);
		return Util::stringToList($fieldString);
	}

	abstract public static function create(array $fields): Field;
	abstract public function parse(): void;
}
