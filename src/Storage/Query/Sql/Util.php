<?php

declare(strict_types=1);

namespace Projom\Storage\Query\Sql;

class Util
{
    public static function padSpaceEven(string $subject): string
    {
        return str_pad($subject, strlen($subject) + 2, ' ', STR_PAD_BOTH);
    }

    public static function join(
        array $list,
        string $delimeter = ','
    ): string {
        return implode($delimeter, $list);
    }

    public static function stringToList(
        array|string $subject,
        string $delimeter = ','
    ): array {
        $list = $subject;
        if (!is_array($list))
            $list = explode($delimeter, static::cleanString($list));

        return $list;
    }

    public static function cleanString(string $subject): string
    {
        return str_replace(' ', '', trim($subject));
    }

    public static function quoteList(array $list): array
    {
        return array_map([self::class, 'quote'], $list);
    }

    public static function quote(string $subject): string
    {
        switch ($subject) {
            case '*':
                return $subject;
            default:
                return "`$subject`";
        }
    }

    public static function quoteValueList(array $list): array
    {
        return array_map([self::class, 'quoteValue'], $list);
    }

    public static function quoteValue(string $subject): string
    {
        switch ($subject) {
            case '*':
                return $subject;
            default:
                return "'$subject'";
        }
    }
}
