<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Query;

use Projom\Storage\Database\Query\Util;
use Projom\Util\Json;

class Field implements AccessorInterface
{
	private array $raw = [];
	private array $fields = [];

	public function __construct(array $fields)
	{
		$this->raw = Util::cleanList($fields);
		$this->fields = $this->build($this->raw);
	}

	public static function create(string ...$fields): Field
	{
		return new Field($fields);
	}

	public function __toString(): string 
	{ 
		return Json::encode($this->get());
	}

	private function build(array $fields): array
	{
		if (!$fields)
			return [];

		if (count($fields) > 1)
			return $fields;

		$fieldString = array_shift($fields);
		return Util::stringToList($fieldString);
	}

	public function get(): array
	{
		return $this->fields;
	}

	public function raw(): array
	{
		return $this->raw;
	}
}