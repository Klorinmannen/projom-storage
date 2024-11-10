<?php

declare(strict_types=1);

namespace Projom\Storage\Engine\Driver\Connection;

use PDO;

use Projom\Storage\Engine\Driver\Connection\ConnectionInterface;

class PDOConnection extends PDO implements ConnectionInterface
{
	const DEFAULT_ATTRIBUTES = [
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
	];

	private int|string $name;

	public function __construct(
		int|string $name,
		string $dsn,
		null|string $username = null,
		null|string $password = null,
		null|array $options = null
	) {

		$this->name = $name;

		$options = $this->parseAttributes($options);
		$options = $options + self::DEFAULT_ATTRIBUTES;
		parent::__construct($dsn, $username, $password, $options);
	}

	public static function create(
		int|string $name,
		string $dsn,
		null|string $username = null,
		null|string $password = null,
		null|array $options = null
	): PDOConnection {
		return new PDOConnection($name, $dsn, $username, $password, $options);
	}

	private function parseAttributes(array $attributes): array
	{
		$parsedAttributes = [];
		foreach ($attributes as $key => $value) {

			// An effort to make constant detection less error-prone.
			$key = strtoupper(trim($key));
			$value = strtoupper(trim($value));

			if (!defined($key) || !defined($value))
				throw new \Exception("The attribute $key or value $value is not a defined constant.", 400);

			$parsedAttributes[constant($key)] = constant($value);
		}

		return $parsedAttributes;
	}

	public function name(): int|string
	{
		return $this->name;
	}
}
