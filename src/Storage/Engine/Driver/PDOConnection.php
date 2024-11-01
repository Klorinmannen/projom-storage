<?php

declare(strict_types=1);

namespace Projom\Storage\Engine\Driver;

use PDO;

use Projom\Storage\Engine\Driver\ConnectionInterface;

class PDOConnection extends PDO implements ConnectionInterface
{
	const DEFAULT_ATTRIBUTES = [
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
	];

	public function __construct(
		string $dsn,
		null|string $username = null,
		null|string $password = null,
		null|array $options = null
	) {
		$options = $this->parseAttributes($options);
		$options = $options + self::DEFAULT_ATTRIBUTES;
		parent::__construct($dsn, $username, $password, $options);
	}

	public static function create(
		string $dsn,
		null|string $username = null,
		null|string $password = null,
		null|array $options = null
	): PDOConnection {
		return new PDOConnection($dsn, $username, $password, $options);
	}

	private function parseAttributes(array $attributes): array
	{
		$parsedAttributes = [];
		foreach ($attributes as $key => $value)
			$parsedAttributes[constant($key)] = constant($value);

		return $parsedAttributes;
	}
}
