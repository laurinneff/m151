<?php

declare(strict_types=1);

namespace App;

class Route
{
    public function __construct(
        public string $path,
        public string $method,
        public array $handler
    ) {
    }
}
