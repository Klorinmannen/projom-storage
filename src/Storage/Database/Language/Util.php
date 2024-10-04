<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Language;

use Projom\Storage\Database\Util as DatabaseUtil;

class Util extends DatabaseUtil
{
    public static function stringToList(array|string $subject, string $delimeter = ','): array
    {
        if (is_array($subject))
            return $subject;

        $subject = static::cleanString($subject);

        $list = [$subject];
        if (strpos($subject, $delimeter) !== false)
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
}
