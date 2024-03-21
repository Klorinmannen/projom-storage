<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Language\Sql;

class Operator
{
    const IS_NULL = 'IS NULL';
    const IS_NOT_NULL = 'IS NOT NULL';
    const NONE = '';

    const EQ = '=';
    const NE = '<>';
    const LT = '<';
    const LTE = '<=';
    const GT = '>';
    const GTE = '>=';
    const IN = 'IN';

    private string $raw = '';
    private string $operator = '';

    public function __construct(string $operator)
    {
        $this->raw = $operator;
        $this->operator = $this->format($operator);
    }

    public function format(string $operator): string
    {
        $operator = strtolower($operator);
        return match ($operator) {
            static::EQ, static::NE, 
            static::LT, static::LTE, 
            static::GT, static::GTE, 
            static::IN => $operator,
            'eq' => static::EQ,
            'ne' => static::NE,
            'lt' => static::LT,
            'lte' => static::LTE,
            'gt' => static::GT,
            'gte' => static::GTE,
            'in' => static::IN,
            default => static::EQ
        };
    }

    public function nullable(): bool
    {
        return match ($this->operator) {
            static::EQ => true,
            static::NE => true,
            default => false
        };
    }

    public function nullString(): string
    {
        return match ($this->operator) {
            static::EQ => static::IS_NULL,
            static::NE => static::IS_NOT_NULL,
            default => static::NONE
        };
    }
        
    public function get(): string
    {
        return $this->operator;
    }

    public function raw(): string
    {
        return $this->raw;
    }

    public static function create(string $operator): Operator
    {
        return new Operator($operator);
    }
}
