<?php

declare(strict_types=1);

namespace Projom\Storage\Database;

interface AccessorInterface
{
	public function get();
	public function __toString(): string;
}
