<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Driver\SQL;

use Projom\Storage\Database\Driver\AccessorInterface;
use Projom\Storage\Database\Driver\SQL\Util;
use Projom\Storage\Database\Query\Sort;

class Order implements AccessorInterface
{
	private array $sortFields = [];
	private array $parsed = [];

	public function __construct(array $sortFields)
	{
		$this->sortFields = $sortFields;
		$this->parse($sortFields);
	}

	public static function create(array $sortFields): Order
	{
		return new Order($sortFields);
	}

	public function __toString(): string
	{
		return Util::join($this->parsed, ', ');
	}

	private function parse(array $sortFields): void
	{
		foreach ($sortFields as $sortField) {
			[$field, $sort] = $sortField;
			$this->parsed[] = $this->createSortField($field, $sort);
		}
	}

	private function createSortField(string $field, Sort $sort): string
	{
		$sortUC = strtoupper($sort->value);
		$quotedField = Util::quote($field);
		return "{$quotedField} {$sortUC}";
	}

	public function get(): array
	{
		return $this->sortFields;
	}

	public function merge(Order ...$others): Order
	{
		foreach ($others as $other) {
			$this->sortFields = array_merge($this->sortFields, $other->get());
			$this->parse($other->get());
		}

		return $this;
	}

	public function empty(): bool
	{
		return empty($this->sortFields);
	}
}
