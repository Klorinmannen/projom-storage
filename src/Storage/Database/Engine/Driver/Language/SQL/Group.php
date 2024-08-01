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

	public function __toString(): string
	{
		return $this->groups;
	}

	public function empty()
	{
		return empty($this->groups);
	}

	private function parse(array $queryFields): void
	{
		$parts = [];
		foreach ($queryFields as $queryField)
			$parts = Util::merge($parts, $this->parseQueryField($queryField));

		$this->groups = Util::join($parts, ', ');
	}

	private function parseQueryField(string $queryField): array
	{
		$parts = [];
		$fields = Util::split($queryField, ',');
		foreach ($fields as $field)
			$parts[] = Util::splitAndQuoteThenJoin($field, '.');

		return $parts;
	}
}
