<?php

declare(strict_types=1);

namespace Projom\Storage\Engine;

use Projom\Storage\Action;

abstract class DriverBase
{
	protected bool $returnSingleRecord = false;

	abstract public function dispatch(Action $action, mixed $args): mixed;

	public function setOptions(array $options): void
	{
		$this->returnSingleRecord = $options['return_single_record'] ?? false;
	}
}
