<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Driver\MySQL;

use Projom\Storage\Database\Query\AccessorInterface;

class Set implements AccessorInterface
{
	private array $fieldsWithValues = [];
	private array $fields = [];
	private array $params = [];
	private string $fieldString = '';
	private int $id = 0;

	public function __construct(array $fieldsWithValues)
	{
		$this->fieldsWithValues = $fieldsWithValues;
		$this->build();
	}

	public static function create(array $fieldsWithValues): Set
	{
		return new Set($fieldsWithValues);
	}

	public function __toString(): string
	{
		return $this->fieldString;
	}

	private function build(): void
	{
		foreach ($this->fieldsWithValues as $field => $value) {
			$this->id++;

			$qoutedField = Util::quote($field);
			$setField = $this->createSetField($field, $this->id);

			$this->fields[] = "{$qoutedField} = :{$setField}";
			$this->params[$setField] = $value;
		}

		$this->fieldString = Util::join($this->fields, ', ');
	}

	private function createSetField(string $field, int $id): string
	{
		return strtolower("set_{$field}_{$id}");
	}

	public function raw()
	{
		return $this->fieldsWithValues;
	}

	public function get()
	{
		return [
			'fields' => $this->fields,
			'params' => $this->params
		];
	}

	public function params(): array
	{
		return $this->params;
	}

	public function asString(): string
	{
		return $this->fieldString;
	}
}
