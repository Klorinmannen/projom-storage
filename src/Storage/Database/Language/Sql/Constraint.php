<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Language\Sql;

class Constraint
{
	private array $constraints = [];

	public function __construct(array $constraints)
	{
		$this->constraints = $constraints;	
	}

	public function parse(): array
	{
		$created = [];
		foreach ($this->constraints as $constraint)
			$created[] = $this->new($constraint);
	
		return $created;
	}

	public function new(array $constraint): array
	{
		$column = array_shift($constraint);
		$value = array_shift($constraint);
		$operator = array_shift($constraint);

		$column = Column::create($column);
		$value = Value::create($value);
		$operator = Operator::create($operator);

		$condition = Condition::create($column, $value, $operator)->format();

		return [ 
			'condition' => $condition->get(), 
			'parameter' => $condition->parameter() 
		];
	}

	public static function EQ(string $column, array $values): array
	{
		$constraints = [];
		foreach ($values as $value)
			$constraints[] = [ 
				'column' => $column, 
				'value' => $value, 
				'operator' => Operator::EQ 
			];

		return $constraints;
	}

	public static function create(array $constraints): Constraint
	{
		return new Constraint($constraints);
	}
}