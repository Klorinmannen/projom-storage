<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Query;

enum Operator
{
	case EQ;
	case NE;
	case GT;
	case LT;
	case GTE;
	case LTE;
	case LIKE;
	case NOT_LIKE;
	case IN;
	case NOT_IN;
	case IS_NULL;
	case IS_NOT_NULL;		
}

class Constraint
{
	private array $fieldsWithValues = [];
	private array $constraints = [];

	public function __construct(array $fieldsWithValues)
	{
		$this->fieldsWithValues = $fieldsWithValues;	
	}

	public function eq(): Constraint
	{
		$this->constraints = $this->build($this->fieldsWithValues, Operator::EQ);
		return $this;
	}

	public function ne(): Constraint
	{
		$this->constraints = $this->build($this->fieldsWithValues, Operator::NE);
		return $this;
	}

	private function build(array $fieldsWithValues, Operator $operator): array
	{
		$constraints = [];

		foreach ($fieldsWithValues as $field => $value) {
			$constraints[] = [
				$field,
				$value,
				$operator
			];
		}

		return $constraints;
	}

	public function get(): array
	{
		return $this->constraints;
	}

	public function merge(Constraint $other): Constraint
	{
		$this->constraints = array_merge($this->constraints, $other->get());
		return $this;
	}

	public static function create(array $fieldsWithValues): Constraint
	{
		return new Constraint($fieldsWithValues);
	}
}