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
	private mixed $raw;
	private mixed $value;
	private ValueType $type;

	public function __construct(mixed $value)
	{
		$this->raw = $value;
		$this->type = $this->setType($value);
		$this->value = $this->format($value);
	}

	public function setType(mixed $value): ValueType
	{
		if (is_string($value))
			return ValueType::STRING;

		if (is_bool($value))
			return ValueType::BOOL;

		if (is_numeric($value))
			return ValueType::NUMERIC;

		if (is_null($value))
			return ValueType::NULL;

		return ValueType::NONE;
	}

	public function format(mixed $value): string|null
	{
		switch ($this->type) {
			case ValueType::NULL:
			case ValueType::STRING:
				return $value;

			case ValueType::BOOL:
				return (string)(int)$value;
			
			case ValueType::NUMERIC:
				return (string)$value;

			case ValueType::NONE:
				return '';
		}
	}

	public function get(): string|null
	{
		return $this->value;
	}

	public function raw(): mixed
	{
		return $this->raw;
	}

	public function type(): ValueType
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
}