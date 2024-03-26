<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Query;

enum Values
{
	case STRING;
	case BOOL;
	case NUMERIC;
	case NULL;
	case NONE;
}

class Value implements AccessorInterface
{
	private mixed $value = [];
	private Values $type = Values::NONE;

	public function __construct(mixed $value)
	{
		$this->value = $value;
		$this->type = $this->type($value);
	}

	public function type(mixed $value): Values
	{
		return match (true) {
			is_string($value) => Values::STRING,
			is_bool($value) => Values::BOOL,
			is_numeric($value) => Values::NUMERIC,
			is_null($value) => Values::NULL,
			default => Values::NONE
		};
	}

	public function get(): mixed
	{
		return $this->value;
	}

	public function raw(): mixed
	{
		return $this->value;
	}

	public function getType(): Values
	{
		return $this->type;
	}

	public function isNull(): bool
	{
		return $this->type === Values::NULL;
	}

	public function empty(): bool
	{
		return $this->type === Values::NONE;
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
