<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Language\Sql;

class Operator
{
    const EQ = '=';
    const NE = '<>';
    const LT = '<';
    const LTE = '<=';
    const GT = '>';
    const GTE = '>=';
    const IN = 'IN';
    const NONE = '';

    private string $raw;
    private string $operator;

    public function __construct(string $operator)
    {
        $this->raw = $operator;
        $this->operator = $this->format($operator);
    }

    public function format(string $operator): string
    {
        $operator = strtolower($operator);
        switch ($operator) {
            case static::EQ:
            case static::NE:
            case static::LT:
            case static::LTE:
            case static::GT:
            case static::GTE:
            case static::IN:
            case static::NONE:
                return $operator;
            case 'eq':
                return static::EQ;
            case 'ne':
                return static::NE;
            case 'lt':
                return static::LT;
            case 'lte':
                return static::LTE;
            case 'gt':
                return static::GT;
            case 'gte':
                return static::GTE;
            case 'in':
                return static::IN;
            default:
                throw new \Exception("Invalid operator: $operator", 400);
        }
    }

    public function nullString(): string
    {
        return match ($this->operator) {
            static::EQ => 'IS NULL',
            static::NE => 'IS NOT NULL',
            default => throw new \Exception("Invalid null operator: $this->operator", 400)
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

    public function empty(): bool
    {
        return $this->operator === static::NONE;
    }
}
