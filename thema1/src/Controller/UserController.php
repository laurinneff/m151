<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\Route;
use App\BaseController;
use App\Config;
use App\Model\User;
use App\ORM;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequest;

class UserController extends BaseController
{
    #[Route('/register', 'POST')]
    public static function register(ServerRequest $request): Response
    {
        $body = json_decode($request->getBody()->getContents());
        $user = new User(
            name: $body->name,
            email: $body->email,
            password: $body->password,
        );
        ORM::insert($user);

        $jwt = self::createJwt($user->getId());

        return new JsonResponse(
            [
                'jwt' => $jwt,
            ],
            201,
        );
    }

    #[Route('/login', 'POST')]
    public static function login(ServerRequest $request): Response
    {
        $body = json_decode($request->getBody()->getContents());
        /** @var User */
        $user = ORM::findOne(User::class, ['email' => $body->email]);
        if ($user === null) {
            return new JsonResponse(
                [
                    'message' => 'Email or password is incorrect',
                ],
                401,
            );
        }

        if (!$user->checkPassword($body->password)) {
            return new JsonResponse(
                [
                    'message' => 'Email or password is incorrect',
                ],
                401,
            );
        }

        $jwt = self::createJwt($user->getId());

        return new JsonResponse(
            [
                'jwt' => $jwt,
            ],
            200,
        );
    }

    #[Route('/me', 'GET')]
    public static function me(ServerRequest $request): Response
    {
        $jwt = $request->getHeader('Authorization')[0] ?? '';
        $jwt = str_replace('Bearer ', '', $jwt);
        $jwt = self::checkJwt($jwt);
        if ($jwt === null) {
            return new JsonResponse(
                [
                    'message' => 'Unauthorized',
                ],
                401,
            );
        }

        /** @var User */
        $user = ORM::findOne(User::class, ['id' => $jwt->userId]);
        if ($user === null) {
            return new JsonResponse(
                [
                    'message' => 'Unauthorized',
                ],
                401,
            );
        }

        return new JsonResponse(
            [
                'name' => $user->getName(),
                'email' => $user->getEmail(),
            ],
            200,
        );
    }

    public static function createJwt(string $userId): string
    {
        $payload = [
            'userId' => $userId,
            'iat' => time(),
            'exp' => time() + 60 * 60 * 24 * 7,
        ];
        $jwt = JWT::encode($payload, Config::getConfig()->getJwtKey(), 'HS256');
        return $jwt;
    }

    public static function checkJwt(string $jwt): ?object
    {
        try {
            $jwt = JWT::decode($jwt, new Key(Config::getConfig()->getJwtKey(), 'HS256'));
            // Check expiration
            if ($jwt->exp < time()) {
                return null;
            }
            return $jwt;
        } catch (\Exception $e) {
            return null;
        }
    }
}
