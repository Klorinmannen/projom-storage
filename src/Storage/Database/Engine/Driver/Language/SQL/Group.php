<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Engine\Driver\Language\SQL;

use Projom\Storage\Database\Engine\Driver\Language\AccessorInterface;
use Projom\Storage\Database\Engine\Driver\Language\Util;

class Group implements AccessorInterface
{
	private readonly string $groups;

	public function __construct(array $fields)
	{
		$this->parse($fields);
	}

	public static function create(array $fields): Group
	{
		return new Group($fields);
	}

	private function parse(array $fields): void
	{
		$parts = [];
		foreach ($fields as $field) {			
			$commaSplitFields = Util::split($field, ',');
			foreach ($commaSplitFields as $commaSplitField)
				$parts[] = Util::splitAndQuoteThenJoin($commaSplitField, '.');
		}

		$this->groups = Util::join($parts, ', ');
	}

	public function empty()
	{
		return empty($this->groups);
	}

	public function __toString(): string
	{
		return $this->groups;
	}
}
