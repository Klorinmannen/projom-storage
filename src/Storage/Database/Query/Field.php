<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Query;

use Projom\Storage\Database\Query\Util;

class Field
{
	private array $fields = [];

	public function __construct(array $fields)
	{
		$this->fields = $this->build($fields);
	}

	public static function create(string ...$fields): Field
	{
		return new Field($fields);
	}

	private function build(array $fields): array
	{
		$fields = Util::cleanList($fields);

		if (!$fields)
			return [];

		if (count($fields) > 1)
			return $fields;

		$fieldString = array_shift($fields);
		return Util::stringToList($fieldString);
	}

	public function get(): array
	{
		return $this->fields;
	}
}
