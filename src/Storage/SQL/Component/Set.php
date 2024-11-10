<?php

declare(strict_types=1);

namespace Projom\Storage\SQL\Component;

use Projom\Storage\SQL\Component\ComponentInterface;
use Projom\Storage\SQL\Util;

class Set implements ComponentInterface
{
	private readonly array $sets;
	private readonly array $fields;
	private readonly array $params;
	private readonly array $positionalParams;
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

		foreach ($fieldsWithValues as $index => $fieldValueList) {
			foreach ($fieldValueList as $field => $value) {
				$this->id++;
				$valueField = $this->createValueField($field, $this->id);
				$quotedField = Util::splitAndQuoteThenJoin($field, '.');
				$sets[] = $this->createSet($quotedField, $valueField);
				$fields[$quotedField] = $quotedField;
				$params[$index][$valueField] = $value;
			}
		}

		$this->sets = $sets;
		$this->fields = $fields;
		$this->params = Util::flatten($params);

		$positionalParams = [];
		foreach ($params as $parameters)
			$positionalParams[] = array_fill(0, count($parameters), '?');

		$this->positionalParams = $positionalParams;
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

	public function positionalParams(): array
	{
		return array_map(
			fn ($positionalParams) => Util::join($positionalParams, ', '),
			$this->positionalParams
		);
	}

	public function positionalParamValues(): array
	{
		return array_values($this->params);
	}
}
