<?php

declare(strict_types=1);

namespace Projom\Storage\Query\Builder\Sql;

use Projom\Storage\Query\Builder\Sql\Util;

class Where
{
    public static function build(array $sqlConditionList): string
    {
        if (!$sqlConditionList)
            return '';

        $clauseList = [];
        foreach ($sqlConditionList as $sqlCondition) {
            $clause = $sqlCondition['clause'];
            $clauseList[] = $clause;

            $predicate = $sqlCondition['predicate'];
            if ($predicate)
                $clauseList[] = Util::padSpaceEven($predicate);
        }

        $conditionString = Util::join($clauseList, '');
        return "WHERE $conditionString";
    }

    public static function namedParameterList(array $sqlConditionList): ?array
    {
        if (!$sqlConditionList)
            return null;

        $namedParameterList = [];
        foreach ($sqlConditionList as $sqlCondition)
            if ($namedParameter = $sqlCondition['named_parameter'] ?? false)
                $namedParameterList[$namedParameter] = $sqlCondition['value'];

        if (!$namedParameterList)
            return null;

        return $namedParameterList;
    }
}
