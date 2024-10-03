<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Language\SQL;

use Projom\Storage\Database\Language\SQL\AccessorInterface;
use Projom\Storage\Database\Language\SQL\Util;
use Projom\Storage\Database\Language\SQL\Util\Sort;

class Order implements AccessorInterface
{
	private readonly array $orders;

	public function __construct(array $sortFields)
	{
		$this->parse($sortFields);
	}

	public static function create(array $sortFields): Order
	{
		return new Order($sortFields);
	}

	public function __toString(): string
	{
		return Util::join($this->orders, ', ');
	}

	public function empty(): bool
	{
		return empty($this->orders);
	}

	private function parse(array $sortFields): void
	{
		$orders = [];
		foreach ($sortFields as [$field, $sort])
			$orders[] = $this->buildSortField($field, $sort);

		$this->orders = $orders;
	}

	private function buildSortField(string $field, Sort $sort): string
	{
		$sortUC = strtoupper($sort->value);
		$quotedField = Util::splitAndQuoteThenJoin($field, '.');
		return "{$quotedField} {$sortUC}";
	}
}
