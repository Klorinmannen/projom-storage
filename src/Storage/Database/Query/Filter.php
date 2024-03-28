<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Query;

use Projom\Storage\Database\Query\Operator;
use Projom\Storage\Database\Query\Operators;
use Projom\Storage\Database\Query\Field;
use Projom\Storage\Database\Query\Value;
use Projom\Util\Json;

class Filter implements AccessorInterface
{
	protected array $fieldsWithValues = [];
	protected array $filters = [];

	public function __construct(array $fieldsWithValues, Operators $operator)
	{
		$this->fieldsWithValues = $fieldsWithValues;
		$this->filters = $this->build($this->fieldsWithValues, $operator);
	}

	public static function create(array $fieldsWithValues, Operators $operator): Filter
	{
		return new Filter($fieldsWithValues, $operator);
	}

	public function __toString(): string 
	{ 
		return Json::encode($this->get());
	}

	protected function build(array $fieldsWithValues, Operators $operator): array
	{
		$Filters = [];

		foreach ($fieldsWithValues as $field => $value) {
			$Filters[] = [
				Field::create($field),
				Operator::create($operator),
				Value::create($value)
			];
		}

		return $Filters;
	}

	public function get(): array
	{
		return $this->filters;
	}

	public function raw(): array
	{
		return $this->fieldsWithValues;
	}

	public function merge(Filter ...$others): Filter
	{
		foreach ($others as $filter)
			$this->filters = [ ...$this->filters, ...$filter->get() ];
		return $this;
	}	
}