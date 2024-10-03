<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Language\SQL\Util;

enum Sort: string
{
	case ASC = 'ASC';
	case DESC = 'DESC';
}
