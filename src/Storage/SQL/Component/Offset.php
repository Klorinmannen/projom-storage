<?php

declare(strict_types=1);

namespace Projom\Storage\SQL\Component;

use Projom\Storage\SQL\ComponentInterface;

class Offset implements ComponentInterface
{
	private readonly int $offset;

	public function __construct(int $offset)
	{
		$this->offset = $offset;
	}

	public static function create(int $offset): Offset
	{
		return new Offset($offset);
	}

	public function __toString(): string
	{
		return (string) $this->offset;
	}

	public function empty(): bool
	{
		return empty($this->offset);
	}
}
