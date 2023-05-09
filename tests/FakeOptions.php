<?php
declare(strict_types=1);

namespace AutoShell;

class FakeOptions implements Options
{
	public function __construct(
		public readonly ?string $foo,
		public readonly ?string $bar,
	) {
	}
}
