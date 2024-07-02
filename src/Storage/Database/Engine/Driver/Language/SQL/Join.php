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
		foreach ($joins as [$join, $onCollectionWithField, $collectionWithField])
			$joinStrings[] = $this->buildJoinString($join, $onCollectionWithField, $collectionWithField);

		$this->joined = Util::join($joinStrings, ' ');
	}

	private function buildJoinString(
		QueryJoin $join,
		string $onCollectionWithField,
		string|null $collectionWithField
	): string {

		if ($collectionWithField === null)
			return $this->buildCustomJoinString($join, $onCollectionWithField);

		$collectionWithField = Util::splitThenQuoteAndJoin($collectionWithField, '.');
		$onCollectionWithField = Util::splitThenQuoteAndJoin($onCollectionWithField, '.');

		[$onCollection, $onCollectionField] = Util::split($onCollectionWithField, '.');

		$joinString = $this->joinString($join->value, $onCollection, $collectionWithField, $onCollectionWithField);

		return $joinString;
	}

	/**
	 * * Format: $join->buildCustomString(QueryJoin::INNER, 'UserRole.UserID = User.UserID')
	 */
	private function buildCustomJoinString(QueryJoin $join,	string $customString): string
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
		string $collectionWithValue,
		string $onCollectionWithValue,
	): string {

		return "{$join} {$onCollection} ON {$collectionWithValue} = {$onCollectionWithValue}";
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
