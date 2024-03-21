<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Language\Sql;

use Projom\Storage\Database\Language\Sql\Column;
use Projom\Storage\Database\Language\Sql\Operator;
use Projom\Storage\Database\Language\Sql\Value;

class Condition
{
    private Column $column;
    private Value $value;
    private Operator $operator;
    private string $condition = '';
    private string $parameterName = '';
    private array|null $parameter = null;

    public function __construct(Column $column, Value $value, Operator $operator)
    {
        $this->column = $column;
        $this->value = $value;
        $this->operator = $operator;
    }

    public function format(): Condition
    {
        if ($this->value->empty())
            return $this;

        $this->parameterName = $this->parameterName();
        $this->condition = $this->condition();
        $this->parameter = [ 
            $this->parameterName => $this->value->get()
        ];

        return $this;
    }

    public function condition(): string
    {        
        $column = $this->column->get();
        $operator = $this->operator->get();
        $namedParameter = $this->parameterName;
     
        if ($this->value->isNull() && $this->operator->nullable())
            return $this->nullCondition();

        return "$column $operator :$namedParameter";
    }

    public function parameterName(): string
    {
        $col = strtolower($this->column->raw());
        $md5_short = substr(md5($this->seed()), -10);
        return 'named_' . $col . '_' . $md5_short;
    }

    public function seed(): string
    {
        return $this->column->raw() . $this->operator->raw() . $this->value->asString();
    }

    public function nullCondition(): string
    {
        $column = $this->column->get();
        $operator = $this->operator->nullString();
        return "$column $operator";
    }

    public function get(): string
    {
        return $this->condition;
    }

    public function parameter(): array|null
    {
        return $this->parameter;
    }

    public static function create(Column $column, Value $value, Operator $operator): Condition
    {
        return new Condition($column, $value, $operator);
    }
}
