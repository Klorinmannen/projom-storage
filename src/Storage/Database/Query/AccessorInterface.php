<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Query;

interface AccessorInterface
{
	public function raw();
	public function get();
	public function __toString(): string;
}