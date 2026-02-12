<?php

declare(strict_types=1);

namespace JRF\Storage\Internal\Database\SQL\Component;

use JRF\Storage\Internal\Database\SQL\Component\ComponentInterface;

class Offset implements ComponentInterface
{
	private readonly null|int $offset;

	public function __construct(null|int $offset)
	{
		$this->offset = $offset;
	}

	public static function create(null|int $offset): Offset
	{
		return new Offset($offset);
	}

	public function __toString(): string
	{
		return (string) $this->offset;
	}

	public function empty(): bool
	{
		return $this->offset === null;
	}
}
