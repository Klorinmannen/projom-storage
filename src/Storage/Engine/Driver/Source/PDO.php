<?php

declare(strict_types=1);

namespace Projom\Storage\Engine\Driver\Source;

class PDO
{
	const DEFAULT_ATTRIBUTES = [
		\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
		\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
	];

	public static function parseAttributes(array $attributes): array
	{
		$parsedAttributes = [];
		foreach ($attributes as $key => $value)
			$parsedAttributes[constant($key)] = constant($value);

		return $parsedAttributes;
	}
}
