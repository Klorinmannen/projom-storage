<?php

declare(strict_types=1);

namespace Projom\Storage\Query;

use Stringable;

enum Format implements Stringable
{
	case ARRAY;
	case STD_CLASS;
	case CUSTOM_OBJECT;

	public function __toString(): string
	{
		return $this->name;
	}
}
