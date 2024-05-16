<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Driver\MySQL;

use Projom\Storage\Database\AccessorInterface;
use Projom\Storage\Database\Driver\MySQL\Util;

class Sort implements AccessorInterface
{
	private array $sortFields = [];
	private array $parsed = [];
	private string $sort = '';

	public function __construct(array $sortFields)
	{
		$this->sortFields = $sortFields;
		$this->parse($sortFields);
	}

	public static function create(array $sortFields): Sort
	{
		return new Sort($sortFields);
	}

	public function __toString(): string
	{
		return $this->string();
	}

	private function parse(array $sortFields)
	{
		foreach ($sortFields as $field => $sort) {
			$sortUC = strtoupper($sort->value);
			$quotedField = Util::quote($field);
			$this->parsed[] = "$quotedField $sortUC";
		}

		$this->sort = Util::join($this->parsed, ', ');
	}

	public function get(): array
	{
		return $this->sortFields;
	}

	public function parsed(): array
	{
		return $this->parsed;
	}

	public function merge(Sort $sort): void
	{
		$this->parse($sort->get());
	}

	public function empty(): bool
	{
		return $this->parsed ? false : true;
	}

	public function string(): string
	{
		return $this->sort;
	}
}
