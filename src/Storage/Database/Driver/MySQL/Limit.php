<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Driver\MySQL;

use Projom\Storage\Database\Driver\AccessorInterface;

class Limit implements AccessorInterface
{
	private int|string $limit = '';
	private string $limitString = '';

	public function __construct(int|string $limit)
	{
		$this->limit = $limit;
		$this->parse($limit);
	}

	public static function create(int|string $limit): Limit
	{
		return new Limit($limit);
	}

	public function __toString(): string
	{
		return $this->limitString;
	}

	public function parse(int|string $limit): void
	{
		$this->limitString = "$limit";
	}

	public function empty(): bool
	{
		return $this->limit ? false : true;
	}
}
