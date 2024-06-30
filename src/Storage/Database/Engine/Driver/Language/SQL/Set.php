<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Engine\Driver\Language\SQL;

use Projom\Storage\Database\Engine\Driver\Language\AccessorInterface;
use Projom\Storage\Database\Engine\Driver\Language\Util;

class Set implements AccessorInterface
{
	private array $fieldsWithValues = [];
	private array $sets = [];
	private array $fields = [];
	private array $params = [];
	private int $id = 0;

	public function __construct(array $fieldsWithValues)
	{
		$this->fieldsWithValues = $fieldsWithValues;
		$this->parse();
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
		return empty($this->fieldsWithValues);
	}

	public function parse(): void
	{
		foreach ($this->fieldsWithValues as $field => $value) {
			$this->id++;
			$valueField = $this->createValueField($field, $this->id);
			$quotedField = Util::quote($field);
			$this->sets[] = $this->createtSet($quotedField, $valueField);
			$this->fields[$valueField] = $quotedField;
			$this->params[$valueField] = $value;
		}
	}

	private function createtSet(string $quotedField, string $valueField): string
	{
		return "{$quotedField} = :{$valueField}";
	}

	private function createValueField(string $field, int $id): string
	{
		return strtolower("set_{$field}_{$id}");
	}

	public function sets(): array
	{
		return $this->sets;
	}

	public function fields(): array
	{
		return $this->fields;
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
