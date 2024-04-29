<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Query;

use Projom\Storage\Database\Query\Util;

class Collection
{
	private string $name;

	public function __construct(string $name)
	{
		$this->name = Util::cleanString($name);
	}

	public static function create(string $name): Collection
	{
		return new Collection($name);
	}

	public function get(): string
	{
		return $this->name;
	}
}
