<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Engine\Driver\Language\SQL;

use Projom\Storage\Database\Engine\Driver\Language\AccessorInterface;
use Projom\Storage\Database\Engine\Driver\Language\Util;

class Group implements AccessorInterface
{
	private array $fields = [];
	private string $group = '';

	public function __construct(array $fields)
	{
		$this->fields = $fields;
		$this->group = $this->parse();
	}

	public static function create(array $fields): Group
	{
		return new Group($fields);
	}

	private function parse(): string
	{
		if (empty($this->fields))
			return '';

		$fields = [];
		foreach ($this->fields as $field)
			$fields[] = Util::stringToList($field);

		$flatten = array_merge(...$fields);

		return Util::quoteAndJoin($flatten, ', ');
	}

	public function empty()
	{
		return empty($this->fields);
	}

	public function __toString(): string
	{
		return $this->group;
	}
}
