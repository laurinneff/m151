<?php

declare(strict_types=1);

namespace App\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Column
{
    public function __construct(
        public ?string $name = null,
        public bool $primaryKey = false,
    ) {
    }
}
