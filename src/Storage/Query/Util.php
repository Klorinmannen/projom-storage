<?php

declare(strict_types=1);

namespace Projom\Storage\Query;

use Projom\Storage\Util as StorageUtil;

class Util extends StorageUtil
{
	public static function stringToArray(string|array $subject): array
	{
		if (is_string($subject))
			$subject = [$subject];

		return $subject;
	}
}
