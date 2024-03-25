<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Query;

use Projom\Storage\Database\Query\Util;

class Collection
{
	private string $raw;
	private string $name;

	public function __construct(string $name)
	{
		$this->raw = Util::cleanString($name);
		$this->name = Util::quote($this->raw);
	}

	public function get(): string
	{
		return $this->name;
	}

	public function raw(): string
	{
		return $this->raw;
	}

	public static function create(string $name): Collection
	{
		return new Collection($name);
	}
}