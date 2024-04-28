<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Query;

enum Sorts: string
{
	case ASC = 'ASC';
	case DESC = 'DESC';
}