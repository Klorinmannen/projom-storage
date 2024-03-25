<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Query;

Interface AccessorInterface
{
	public function raw();
	public function get();
}