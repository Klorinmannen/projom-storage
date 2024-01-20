<?php

declare(strict_types=1);

namespace Projom\Storage\Query\Sql;

use Projom\Storage\Query\Sql\Util;
use Projom\Storage\Query\Sql\Operator;

class Condition
{
    public static function buildList(array $conditionList): array
    {
        if (!$conditionList)
            return [];

        return array_map([self::class, 'build'], $conditionList);
    }

    public static function build(array $condition): array
    {
        if (!$condition)
            return [];

        $operator = $condition['operator'];
        switch ($operator) {
            case Operator::IN:
                return static::inCondition($condition);

            case Operator::EQ:
            case Operator::NE:
            case Operator::LT:
            case Operator::LTE:
            case Operator::GT:
            case Operator::GTE:
                return static::condition($condition);

            default:
                throw new \Exception("Internal server error: query\condition::build $operator", 500);
        }
    }

    public static function inCondition(array $condition): array
    {
        $value = $condition['value'];
        $inClause = static::inClause($value);

        $column = $condition['column'];
        $quotedColumn = Util::quote($column);

        return [
            'clause' => "$quotedColumn $inClause",
            'predicate' => $condition['predicate'] ?? false
        ];
    }

    public static function inClause(array|string $value): string
    {
        $valueList = Util::stringToList($value);
        $quotedValueList = Util::quoteValueList($valueList);
        $quotedValueString = Util::join($quotedValueList);
        return "IN ($quotedValueString)";
    }

    public static function condition(array $condition): array
    {
        $namedParameter = static::namedParameter($condition);
        $clause = static::clause($condition);
        return [
            'clause' => $clause,
            'named_parameter' => $namedParameter,
            'value' => $condition['value'],
            'predicate' => $condition['predicate'] ?? false
        ];
    }

    public static function namedParameter(array $condition): string
    {
        $column = $condition['column'];
        $operator = $condition['operator'];
        $value = $condition['value'];

        $subject = strtolower($column);
        $md5_short = substr(md5($subject . $operator . $value), -10);
        return 'named_' . $subject . '_' . $md5_short;
    }

    public static function clause(array $condition): string
    {
        $column = $condition['column'];
        $quotedColumn = Util::quote($column);
        $operator = $condition['operator'];
        $namedParameter = static::namedParameter($condition);

        if ($predicate = $condition['predicate'] ?? '')
            $predicate = Util::padSpaceEven($predicate);

        return "$quotedColumn $operator :$namedParameter$predicate";
    }
}
