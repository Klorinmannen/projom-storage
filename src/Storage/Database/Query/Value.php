<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Query;

use Projom\Storage\Database\Query\Values;

class Value implements AccessorInterface
{
	private mixed $value = [];
	private Values $type = Values::NONE;

	public function __construct(mixed $value)
	{
		$this->value = $value;
		$this->type = $this->type($value);
	}

	public static function create(mixed $value): Value
	{
		return new Value($value);
	}

	public function __toString(): string
	{
		return $this->asString();
	}

	public function type(mixed $value): Values
	{
		return match (true) {
			is_string($value) => Values::STRING,
			is_bool($value) => Values::BOOL,
			is_numeric($value) => Values::NUMERIC,
			is_null($value) => Values::NULL,
			is_array($value) => Values::ARRAY,
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
		return match ($this->type) {
			Values::STRING => $this->value,
			Values::BOOL => $this->value ? 'TRUE' : 'FALSE',
			Values::NUMERIC => (string) $this->value,
			Values::NULL => 'NULL',
			Values::ARRAY => implode(',', $this->value),
			default => ''
		};
	}
}
