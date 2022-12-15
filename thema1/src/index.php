<?php

namespace App;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequestFactory;

require_once __DIR__.'/../vendor/autoload.php';

$whoops = new \Whoops\Run();
$errorHandler = new \Whoops\Handler\PrettyPageHandler();
$errorHandler->setEditor('vscode');
$whoops->pushHandler($errorHandler);
$whoops->register();

$routes = [];

// Find controllers in the Controller directory
$controllerFiles = glob(__DIR__.'/Controller/*.php');
foreach ($controllerFiles as $controllerFile) {
    $controllerClass = 'App\\Controller\\'.basename($controllerFile, '.php');
    $routes = array_merge($routes, $controllerClass::getRoutes());
}

$request = ServerRequestFactory::fromGlobals(
    $_SERVER,
    $_GET,
    $_POST,
    $_COOKIE,
    $_FILES
);

foreach ($routes as $route) {
    if ($route->path === $request->getUri()->getPath() && $route->method === $request->getMethod()) {
        $errorHandler->addDataTable('Route', [
            'Controller' => $route->handler[0],
            'Handler' => $route->handler[1]
        ]);
        $response = call_user_func($route->handler, $request);
        break;
    }
}

if (!isset($response)) {
    $response = new Response();
    $response->getBody()->write('Not Found');
    $response = $response->withStatus(404);
}

http_response_code($response->getStatusCode());

foreach ($response->getHeaders() as $name => $values) {
    foreach ($values as $value) {
        header(sprintf('%s: %s', $name, $value), false);
    }
}

echo $response->getBody();
