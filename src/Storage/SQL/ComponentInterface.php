<?php

declare(strict_types=1);

namespace Projom\Storage\SQL;

interface ComponentInterface
{
	public function empty();
	public function __toString(): string;
}
