<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Language\SQL;

use Projom\Storage\Database\Language\AccessorInterface;

class Limit implements AccessorInterface
{
	private readonly int|string $limit;

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
