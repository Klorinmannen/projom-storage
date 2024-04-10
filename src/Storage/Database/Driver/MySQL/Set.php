<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Driver\MySQL;

use Projom\Storage\Database\Query\AccessorInterface;

class Set implements AccessorInterface
{
	private array $fieldsWithValues = [];
	private array $sets = [];
	private array $fields = [];
	private array $params = [];
	private string $setString = '';
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
		return $this->asString();
	}

	private function build(): void
	{
		foreach ($this->fieldsWithValues as $field => $value) {
			$this->id++;
			$valueField = $this->createValueField($field, $this->id);
			$qoutedField = Util::quote($field);
			$this->sets[] = "{$qoutedField} = :{$valueField}";
			$this->fields[$valueField] = $qoutedField;
			$this->params[$valueField] = $value;
		}

		$this->setString = Util::join($this->sets, ', ');
	}

	private function createValueField(string $field, int $id): string
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
			'sets' => $this->sets,
			'fields' => $this->fields,
			'params' => $this->params
		];
	}

	public function sets(): array
	{
		return $this->sets;
	}

	public function asString(): string
	{
		return $this->setString;
	}

	public function fields(): array
	{
		return $this->fields;
	}

	public function fieldString(): string
	{
		return implode(', ', $this->fields());
	}

	public function valueFields(): array
	{
		return array_keys($this->fields);
	}

	public function params(): array
	{
		return $this->params;
	}

	public function positionalString(): string
	{
		return implode(
			', ',
			array_fill(0, count($this->params()), '?')
		);
	}

	public function positionalParams(): array
	{
		return array_values($this->params());
	}
}
