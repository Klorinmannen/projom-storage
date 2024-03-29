<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Query;

use Projom\Storage\Database\Query\Operators;

class Operator implements AccessorInterface
{
    private Operators $raw = '';
    private string $operator = '';

    public function __construct(Operators $operator)
    {
        $this->raw = $operator;
        $this->operator = $operator->value;
    }

    public static function create(Operators $operator): Operator
    {
        return new Operator($operator);
    }
    
    public function __toString(): string 
    { 
        return $this->get();
    }
       
    public function get(): string
    {
        return $this->operator;
    }

    public function raw(): Operators
    {
        return $this->raw;
    }
}
