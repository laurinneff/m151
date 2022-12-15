<?php

declare(strict_types=1);

namespace App\Controller;

use App\BaseController;
use App\Attribute\Route;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;

class TestController extends BaseController
{
    #[Route('/test', 'GET')]
    public static function test(ServerRequest $request): Response
    {
        $response = new Response();
        $response->getBody()->write('Hello World');
        return $response;
    }
}
