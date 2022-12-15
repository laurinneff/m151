<?php

namespace App;

use App\Attribute\Route as RouteAttribute;

abstract class BaseController
{
    public static function getRoutes()
    {
        if (static::class === self::class) {
            throw new \Exception('BaseController cannot be used directly');
        }

        $routes = [];
        $class = new \ReflectionClass(static::class);
        foreach ($class->getMethods() as $method) {
            $attributes = $method->getAttributes(RouteAttribute::class);
            if (count($attributes) === 0) {
                continue;
            }

            if (!$method->isStatic()) {
                throw new \Exception('Route handler must be static');
            }

            $route = $attributes[0]->newInstance();
            $routes[] = new Route(
                $route->path,
                $route->method,
                [static::class, $method->getName()]
            );
        }

        return $routes;
    }
}
