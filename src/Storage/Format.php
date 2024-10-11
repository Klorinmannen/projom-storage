<?php

declare(strict_types=1);

namespace Projom\Storage;

enum Format
{
	case ARRAY;
	case JSON;
	case STD_CLASS;
	case CUSTOM_OBJECT;
}