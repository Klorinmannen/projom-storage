<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Driver\SQL;

use Projom\Storage\Database\Driver\AccessorInterface;

class Limit implements AccessorInterface
{
	private int|string|null $limit = null;

	public function __construct(int|string $limit)
	{
		$this->limit = $limit;
	}

	public static function create(int|string $limit): Limit
	{
		return new Limit($limit);
	}

	public function __toString(): string
	{
		return (string) $this->limit;
	}

	public function empty(): bool
	{
		return empty($this->limit);
	}
}
