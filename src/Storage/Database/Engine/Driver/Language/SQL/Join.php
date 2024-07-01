<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Engine\Driver\Language\SQL;

use Projom\Storage\Database\Engine\Driver\Language\AccessorInterface;
use Projom\Storage\Database\Engine\Driver\Language\Util;
use Projom\Storage\Database\Query\Join as QueryJoin;

class Join implements AccessorInterface
{
	private array $joins = [];
	private string $joined = '';

	public function __construct(array $joins)
	{
		$this->joins = $joins;
		$this->parse();
	}

	public static function create(array $joins): Join
	{
		return new Join($joins);
	}

	private function parse(): void
	{
		$joinStrings = [];
		foreach ($this->joins as [$collection, $collectionField, $joinType, $otherCollection, $otherCollectionField])
			$joinStrings[] = $this->buildJoinString(
				$collection,
				$collectionField,
				$joinType,
				$otherCollection,
				$otherCollectionField
			);

		$this->joined = implode(' ', $joinStrings);
	}

	private function buildJoinString(
		string $collection,
		string $collectionField,
		QueryJoin $join,
		string $otherCollection,
		string $otherCollectionField
	): string {

		$collectionWithField = Util::quoteAndJoin([$collection, $collectionField], '.');

		$otherCollection = Util::quote($otherCollection);
		$otherCollectionField = Util::quote($otherCollectionField);
		$otherCollectionWithField = Util::join([$otherCollection, $otherCollectionField], '.');

		$joinString = "{$join->value} {$otherCollection} ON {$collectionWithField} = {$otherCollectionWithField}";

		return $joinString;
	}

	public function empty()
	{
		return empty($this->joins);
	}

	public function __toString(): string
	{
		return $this->joined;
	}
}
