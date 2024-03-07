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
    private string $namedCondition = '';
    private array|null $namedParamater = null;

    public function __construct(Column $column, Value $value, Operator $operator)
    {
        $this->column = $column;
        $this->value = $value;
        $this->operator = $operator;
        $this->format();
    }

    public function format(): void
    {
        if ($this->value->empty())
            return;

        if ($this->value->isNull())
            return $this->nullCondition($this->column->get(), $this->operator->nullString());

        $namedParameter = static::namedParameter($this->column->raw());
        $this->namedCondition = $this->namedCondition($this->column->get(), $this->operator->get(), $namedParameter);

        $this->namedParamater = [ 
            $namedParameter => $this->value->get()
        ];
    }

    public function namedCondition(string $column, string $operator, string $namedParameter): string
    {        
        return "$column $operator :$namedParameter";
    }

    public function namedParameter(string $column): string
    {
        $md5_short = substr(md5($column), -10);
        return 'named_' . strtolower($column) . '_' . $md5_short;
    }

    public function nullCondition(string $column, string $operator): void
    {
        $this->namedCondition = "$column $operator";
    }

    public function getNamedCondition(): string
    {
        return $this->namedCondition;
    }

    public function getNamedParameter(): array|null
    {
        return $this->namedParamater;
    }
}
