<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Query\Constraint;

use Projom\Storage\Database\Query\Constraint;
use Projom\Storage\Database\Query\Operators;

class Equals extends Constraint
{
	public function __construct(array $fieldsWithValues)
	{
		$this->fieldsWithValues = $fieldsWithValues;
		$this->constraints = $this->build($this->fieldsWithValues, Operators::EQ);
	}

	public static function create(array $fieldsWithValues): Equals
	{
		return new Equals($fieldsWithValues);
	}
}
