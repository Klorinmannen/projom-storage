<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Language\SQL;

use Projom\Storage\Database\Language\SQL\AccessorInterface;
use Projom\Storage\Database\Language\SQL\Util;
use Projom\Storage\Database\MySQL\Join as MySQLJoin;

class Join implements AccessorInterface
{
	private readonly string $joined;

	public function __construct(array $joins)
	{
		$this->parse($joins);
	}

	public static function create(array $joins): Join
	{
		return new Join($joins);
	}

	public function __toString(): string
	{
		return $this->joined;
	}

	public function empty()
	{
		return empty($this->joined);
	}

	private function parse(array $joins): void
	{
		$joinStrings = [];
		foreach ($joins as [$currentCollectionWithField, $join, $onCollectionWithField])
			$joinStrings[] = $this->buildJoinString($currentCollectionWithField, $join, $onCollectionWithField);

		$this->joined = Util::join($joinStrings, ' ');
	}

	private function buildJoinString(
		string $currentCollectionWithField,
		MySQLJoin $join,
		string|null $onCollectionWithField
	): string {

		if ($onCollectionWithField === null)
			return $this->buildCustomJoinString($currentCollectionWithField, $join);

		$currentCollectionWithField = Util::splitThenQuoteAndJoin($currentCollectionWithField, '.');
		$onCollectionWithField = Util::splitThenQuoteAndJoin($onCollectionWithField, '.');

		[$onCollection, $onCollectionField] = Util::split($onCollectionWithField, '.');

		$joinString = $this->joinString($join->value, $onCollection, $currentCollectionWithField, $onCollectionWithField);

		return $joinString;
	}

	/**
	 * * Format: $join->buildCustomString('UserRole.UserID = User.UserID', QueryJoin::INNER)
	 */
	private function buildCustomJoinString(string $customString, MySQLJoin $join): string
	{
		$stringParts = Util::split($customString, '=');

		$firstParts = Util::split(array_shift($stringParts), '.');
		$secondParts = Util::split(array_shift($stringParts), '.');

		$onCollection = Util::quote($secondParts[0]);
		$onCollectionWithValue = Util::quoteAndJoin($secondParts, '.');

		$currentCollectionWithValue = Util::quoteAndJoin($firstParts, '.');

		$joinString = $this->joinString($join->value, $onCollection, $currentCollectionWithValue, $onCollectionWithValue);

		return $joinString;
	}

	private function joinString(
		string $join,
		string $onCollection,
		string $currentCollection,
		string $on,
	): string {

		return "{$join} {$onCollection} ON {$currentCollection} = {$on}";
	}
}
