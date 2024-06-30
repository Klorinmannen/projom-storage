<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Engine\Driver\Language;

use Projom\Storage\Database\Util as DBUtil;

class Util extends DBUtil
{
    public static function quoteList(array $list): array
    {
        return array_map([static::class, 'quote'], $list);
    }

    public static function quote(string $subject): string
    {
        $subject = static::cleanString($subject);

        if ($subject === '*')
            return $subject;

        return "`$subject`";
    }

    public static function quoteAndJoin(array $list, string $delimeter = ','): string
    {
        return static::join(static::quoteList($list), $delimeter);
    }
}
