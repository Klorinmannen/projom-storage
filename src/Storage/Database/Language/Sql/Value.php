<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Language\Sql;

enum ValueType
{
	case STRING;
	case BOOL;
	case NUMERIC;
	case NULL;
	case NONE;
}

class Value
{
	private mixed $value = [];
	private ValueType $type = ValueType::NONE;

	public function __construct(mixed $value)
	{
		$this->value = $value;
		$this->type = $this->type($value);
	}

	public function type(mixed $value): ValueType
	{
		return match (true) {
			is_string($value) => ValueType::STRING,
			is_bool($value) => ValueType::BOOL,
			is_numeric($value) => ValueType::NUMERIC,
			is_null($value) => ValueType::NULL,
			default => ValueType::NONE
		};
	}

	public function get(): string|null
	{
		return $this->value;
	}

	public function getType(): ValueType
	{
		return $this->type;
	}

	public function isNull(): bool
	{
		return $this->type === ValueType::NULL;
	}

	public function empty(): bool
	{
		return $this->type === ValueType::NONE;
	}

	public function asString(): string
	{
		return (string)$this->value;
	}

	public static function create(mixed $value): Value
	{
		return new Value($value);
	}
}
