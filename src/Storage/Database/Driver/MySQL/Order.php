<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Driver\MySQL;

use Projom\Storage\Database\Driver\AccessorInterface;
use Projom\Storage\Database\Driver\MySQL\Util;

class Order implements AccessorInterface
{
	private array $sortFields = [];
	private array $parsed = [];
	private string $sortBy = '';

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
		return $this->string();
	}

	private function parse(array $sortFields)
	{
		foreach ($sortFields as $sortField) {

			[$sort, $field] = $sortField;
			$sortUC = strtoupper($sort->value);
			$quotedField = Util::quote($field);

			$this->parsed[] = "$quotedField $sortUC";
		}

		$this->sortBy = Util::join($this->parsed, ', ');
	}

	public function get(): array
	{
		return $this->sortFields;
	}

	public function parsed(): array
	{
		return $this->parsed;
	}

	public function merge(Order $sort): void
	{
		$this->parse($sort->get());
	}

	public function empty(): bool
	{
		return $this->parsed ? false : true;
	}

	public function string(): string
	{
		return $this->sortBy;
	}
}
