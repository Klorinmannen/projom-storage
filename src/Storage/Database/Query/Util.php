<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Query;

class Util
{
    public static function padSpaceEven(string $subject): string
    {
        return str_pad($subject, strlen($subject) + 2, ' ', STR_PAD_BOTH);
    }

    public static function stringToList(array|string $subject, string $delimeter = ','): array
    {
        if (is_array($subject))
            return $subject;

        $subject = static::cleanString($subject);

        $list = [$subject];
        if (strpos($subject, ',') !== false)
            $list = explode($delimeter, $subject);

        return $list;
    }

    public static function cleanString(string $subject): string
    {
        return str_replace(' ', '', trim($subject));
    }

    public static function cleanList(array $list): array
    {
        return array_map([self::class, 'cleanString'], $list);
    }

    public static function clean(array|string $subject): array|string
    {
        if (is_array($subject))
            return static::cleanList($subject);
        return static::cleanString($subject);
    }

    public static function join(array $list, string $delimeter = ','): string
    {
        return implode($delimeter, $list);
    }
}
