<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Query;

use Projom\Storage\Database\Query\Util;

class Collection implements AccessorInterface
{
	private string $raw;
	private string $name;

	public function __construct(string $name)
	{
		$this->raw = Util::cleanString($name);
		$this->name = $this->raw;
	}

	public static function create(string $name): Collection
	{
		return new Collection($name);
	}
	
	public function __toString(): string 
	{ 
		return $this->get();
	}

	public function get(): string
	{
		return $this->name;
	}

	public function raw(): string
	{
		return $this->raw;
	}
}