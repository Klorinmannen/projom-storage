<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Engine\Driver\Language\SQL;

use Projom\Storage\Database\Engine\Driver\Language\AccessorInterface;
use Projom\Storage\Database\Engine\Driver\Language\Util;
use Projom\Storage\Database\Query\Join as QueryJoin;

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

	private function parse(array $joins): void
	{
		$joinStrings = [];
		foreach ($joins as [$currentCollectionWithField, $join, $onCollectionWithField])
			$joinStrings[] = $this->buildJoinString($currentCollectionWithField, $join, $onCollectionWithField);

		$this->joined = Util::join($joinStrings, ' ');
	}

	private function buildJoinString(
		string $currentCollectionWithField,
		QueryJoin $join,
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
	 * * Format: $join->buildCustomString(QueryJoin::INNER, 'UserRole.UserID = User.UserID')
	 */
	private function buildCustomJoinString(string $customString, QueryJoin $join): string
	{
		$stringParts = Util::split($customString, '=');

		$firstParts = Util::split(array_shift($stringParts), '.');
		$onCollection = Util::quote($firstParts[0]);
		$onCollectionWithValue = Util::quoteAndJoin($firstParts, '.');

		$collectionWithValue = Util::splitThenQuoteAndJoin(array_shift($stringParts), '.');

		$joinString = $this->joinString($join->value, $onCollection, $collectionWithValue, $onCollectionWithValue);

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

	public function empty()
	{
		return empty($this->joined);
	}

	public function __toString(): string
	{
		return $this->joined;
	}
}
