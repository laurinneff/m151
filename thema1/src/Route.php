<?php

namespace App;

class Route
{
    public function __construct(
        public string $path,
        public string $method = 'GET',
        public array $handler
    ) {
    }
}
