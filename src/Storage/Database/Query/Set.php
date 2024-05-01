<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Query;

abstract class Set
{
	abstract public static function create(array $fieldsWithValues): Set;
	abstract public function parse(): void;
}