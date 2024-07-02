<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Engine\Driver\Language\SQL;

use Projom\Storage\Database\Engine\Driver\Language\AccessorInterface;
use Projom\Storage\Database\Engine\Driver\Language\Util;

class Set implements AccessorInterface
{
	private readonly array $sets;
	private readonly array $fields;
	private readonly array $params;
	private int $id = 0;

	public function __construct(array $fieldsWithValues)
	{
		$this->parse($fieldsWithValues);
	}

	public static function create(array $fieldsWithValues): Set
	{
		return new Set($fieldsWithValues);
	}

	public function __toString(): string
	{
		return Util::join($this->sets, ', ');
	}

	public function empty(): bool
	{
		return empty($this->sets);
	}

	public function parse(array $fieldsWithValues): void
	{
		$sets = [];
		$fields = [];
		$params = [];
		
		foreach ($fieldsWithValues as $field => $value) {
			$this->id++;
			$valueField = $this->createValueField($field, $this->id);
			$quotedField = Util::splitAndQuoteThenJoin($field, '.');
			$sets[] = $this->createSet($quotedField, $valueField);
			$fields[$valueField] = $quotedField;
			$params[$valueField] = $value;
		}

		$this->sets = $sets;
		$this->fields = $fields;
		$this->params = $params;
	}

	private function createSet(string $quotedField, string $valueField): string
	{
		return "{$quotedField} = :{$valueField}";
	}

	private function createValueField(string $field, int $id): string
	{
		$field = str_replace(['.', ','], '_', $field);
		$field = strtolower($field);
		return "set_{$field}_{$id}";
	}

	public function params(): array
	{
		return $this->params;
	}

	public function positionalFields(): string
	{
		return Util::join($this->fields, ', ');
	}

	public function positionalParams(): string
	{
		$positionalParams = array_fill(0, count($this->params), '?');
		return Util::join($positionalParams, ', ');
	}

	public function positionalParamValues(): array
	{
		return array_values($this->params);
	}
}
