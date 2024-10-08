<?php

declare(strict_types=1);

namespace Projom\Storage\SQL;

use Projom\Storage\Util as StorageUtil;

class Util extends StorageUtil
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

    public static function splitThenQuoteAndJoin(string $subject, string $delimeter = ','): string
    {
        return static::quoteAndJoin(static::split($subject, $delimeter), $delimeter);
    }

    public static function splitAndQuote(string $subject, string $delimeter = ','): array
    {
        return static::quoteList(static::split($subject, $delimeter));
    }

    public static function splitAndQuoteThenJoin(string $subject, string $delimeter = ','): string
    {
        return static::join(static::splitAndQuote($subject, $delimeter), $delimeter);
    }
}
