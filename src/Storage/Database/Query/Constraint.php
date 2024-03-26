<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Query;

use Projom\Storage\Database\Query\Operator;
use Projom\Storage\Database\Query\Operators;
use Projom\Storage\Database\Query\Field;
use Projom\Storage\Database\Query\Value;

abstract class Constraint implements AccessorInterface
{
	protected array $fieldsWithValues = [];
	protected array $constraints = [];

	protected function build(array $fieldsWithValues, Operators $operator): array
	{
		$constraints = [];

		foreach ($fieldsWithValues as $field => $value) {
			$constraints[] = [
				Field::create($field),
				Value::create($value),
				Operator::create($operator)
			];
		}

		return $constraints;
	}

	public function get(): array
	{
		return $this->constraints;
	}

	public function raw(): array
	{
		return $this->fieldsWithValues;
	}

	public function merge(Constraint ...$other): Constraint
	{
		foreach ($other as $constraint)
			$this->constraints = [ ...$this->constraints, ...$constraint->get() ];
		return $this;
	}
}