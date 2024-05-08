<?php

declare(strict_types=1);

namespace Projom\Storage\Query;

enum Sorts: string
{
	case ASC = 'ASC';
	case DESC = 'DESC';
}