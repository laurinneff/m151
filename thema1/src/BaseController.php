<?php

namespace App;

use App\Attribute\Route as RouteAttribute;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;

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

            if (!$method->isPublic()) {
                throw new \Exception('Route handler must be public');
            }

            $params = $method->getParameters();
            if (count($params) !== 1 || $params[0]->getType() === null || $params[0]->getType()->getName() !== ServerRequest::class) {
                throw new \Exception('Route handler must accept a ServerRequest as its only parameter');
            }

            $returnType = $method->getReturnType();
            if ($returnType === null || $returnType->getName() !== Response::class) {
                throw new \Exception('Route handler must return a Response');
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
