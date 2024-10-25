<?php

declare(strict_types=1);

namespace Projom\Storage\SQL\Component;

use Projom\Storage\SQL\ComponentInterface;

class Limit implements ComponentInterface
{
	private readonly null|int $limit;

	public function __construct(null|int $limit)
	{
		$this->limit = $limit;
	}

	public static function create(null|int $limit): Limit
	{
		return new Limit($limit);
	}

	public function __toString(): string
	{
		return (string) $this->limit;
	}

	public function empty(): bool
	{
		return $this->limit === null;
	}
}
