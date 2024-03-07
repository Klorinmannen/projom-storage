<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Language\Sql\Query;

use Projom\Storage\Database\Language\Sql\Column;
use Projom\Storage\Database\Language\Sql\Condition;
use Projom\Storage\Database\Language\Sql\Operator;
use Projom\Storage\Database\Language\Sql\Table;
use Projom\Storage\Database\Language\Sql\Value;

class Select
{
	private Table $table;
	private Column $column;
	private Value $value;
	private Operator $operator;
	private Condition $condition;

	public function __construct(Table $table, Column $column, Value $value, Operator $operator)
	{
		$this->table = $table;
		$this->column = $column;
		$this->value = $value;
		$this->operator = $operator;
		$this->condition = new Condition($this->column, $this->value, $this->operator);
	}

	public function query(): string
	{
		$columns = $this->column->get();
		$table = $this->table->get();
		$condition = $this->condition->getNamedCondition();

		if ($condition)
			return "SELECT $columns FROM $table WHERE $condition";

		return "SELECT $columns FROM $table";
	}

	public function params(): ?array
	{
		return $this->condition->getNamedParameter();
	}

	public static function create(string $table, string $column, mixed $value, string $operator): Select
	{
		$table = new Table($table);
		$column = new Column($column);
		$value = new Value($value);
		$operator = new Operator($operator);	
		return new Select($table, $column, $value, $operator);
	}
}
