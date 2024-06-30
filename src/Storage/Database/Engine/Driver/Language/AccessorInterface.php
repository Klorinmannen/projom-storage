<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Engine\Driver\Language;

interface AccessorInterface
{
	public function empty();
	public function __toString(): string;
}
