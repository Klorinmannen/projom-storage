<?php

declare(strict_types=1);

namespace Projom\Storage\Query;

enum Format
{
	case ARRAY;
	case STD_CLASS;
	case CUSTOM_OBJECT;
}