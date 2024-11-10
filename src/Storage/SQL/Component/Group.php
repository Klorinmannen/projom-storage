<?php

declare(strict_types=1);

namespace Projom\Storage\SQL\Component;

use Projom\Storage\SQL\Component\ComponentInterface;
use Projom\Storage\SQL\Util;

class Group implements ComponentInterface
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

	private function parse(array $groupOn): void
	{
		$parts = [];
		foreach ($groupOn as $gruopOnSets)
			foreach ($gruopOnSets as $groupOnSet)
				$parts = Util::merge($parts, $this->parseGruopOnSet($groupOnSet));

		$this->groups = Util::join($parts, ', ');
	}

	private function parseGruopOnSet(string $gruopOnSet): array
	{
		$parts = [];

		$fields = Util::split($gruopOnSet, ',');
		foreach ($fields as $field)
			$parts[] = Util::splitAndQuoteThenJoin($field, '.');

		return $parts;
	}
}
