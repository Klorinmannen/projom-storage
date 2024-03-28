<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Driver\MySQL;

use Projom\Storage\Database\Query\Util as QUtil;

class Util extends QUtil
{
	public static function quoteList(array $list): array
    {
        return array_map([ self::class, 'quote' ], $list);
    }

    public static function quote(string $subject): string
    {
        if ($subject == '*')
            return $subject;
        return "`$subject`";
    }

    public static function quoteAndJoin(array $list, string $delimeter = ','): string
    {
        return static::join(static::quoteList($list), $delimeter);
    }
}