<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Driver\MySQL;

use Projom\Storage\Database\Query\AccessorInterface;
use Projom\Storage\Database\Driver\MySQL\Util;

class Sort implements AccessorInterface
{
	private array $raw = [];
	private array $parsed = [];
	private string $sort = '';

	public function __construct(array $sortFields)
	{
		$this->raw = $sortFields;
		$this->build();
	}

	public static function create(array $sortFields): Sort
	{
		return new Sort($sortFields);
	}

	public function __toString(): string 
	{ 
		return $this->get();
	}

	private function build() 
	{ 
		foreach ($this->raw as $sort) {

			[$field, $sorting] = $sort;
			$sorting = strtoupper($sorting);
			$this->parsed[] = "$field $sorting";
		}

		$this->sort = Util::join($this->parsed);
	}

	public function raw(): array 
	{ 
		return $this->raw; 
	}

	public function get(): string
	{ 
		return $this->sort;
	}
}