<?php

declare(strict_types=1);

namespace Projom\Storage\Database\MySQL;

enum Sort: string
{
	case ASC = 'ASC';
	case DESC = 'DESC';
}
