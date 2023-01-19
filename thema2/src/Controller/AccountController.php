<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\Route;
use App\BaseController;
use App\Config;
use App\Model\Account;
use App\Model\User;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequest;

class AccountController extends BaseController
{
    #[Route('/account', 'GET')]
    public static function list(ServerRequest $request): Response
    {
        $jwt = $request->getHeader('Authorization')[0] ?? '';
        $jwt = str_replace('Bearer ', '', $jwt);
        $jwt = UserController::checkJwt($jwt);
        if ($jwt === null) {
            return new JsonResponse(
                [
                    'message' => 'Unauthorized',
                ],
                401,
            );
        }

        /** @var ?User */
        $user = Config::get()->entityManager()->getRepository(User::class)->find($jwt->userId);
        if ($user === null) {
            return new JsonResponse(
                [
                    'message' => 'Unauthorized',
                ],
                401,
            );
        }

        /** @var Account[] */
        $accounts = $user->getAccounts()->toArray();

        return new JsonResponse(
            array_map(
                fn (Account $account) =>
                    [
                        'id' => $account->getId(),
                        'name' => $account->getName(),
                        'balance' => $account->getBalance(),
                    ],
                $accounts
            ),
        );
    }

    #[Route('/account', 'POST')]
    public static function create(ServerRequest $request): Response
    {
        $jwt = $request->getHeader('Authorization')[0] ?? '';
        $jwt = str_replace('Bearer ', '', $jwt);
        $jwt = UserController::checkJwt($jwt);
        if ($jwt === null) {
            return new JsonResponse(
                [
                    'message' => 'Unauthorized',
                ],
                401,
            );
        }

        /** @var ?User */
        $user = Config::get()->entityManager()->getRepository(User::class)->find($jwt->userId);
        if ($user === null) {
            return new JsonResponse(
                [
                    'message' => 'Unauthorized',
                ],
                401,
            );
        }

        $body = json_decode($request->getBody()->getContents());
        $account = new Account();
        $account->setName($body->name);
        $account->setUser($user);
        $account->setBalance(0);
        Config::get()->entityManager()->persist($account);
        Config::get()->entityManager()->flush();

        return new JsonResponse(
            [
                'id' => $account->getId(),
                'name' => $account->getName(),
                'balance' => $account->getBalance(),
            ],
            201,
        );
    }

    #[Route('/account', 'PUT')]
    public static function update(ServerRequest $request): Response
    {
        $jwt = $request->getHeader('Authorization')[0] ?? '';
        $jwt = str_replace('Bearer ', '', $jwt);
        $jwt = UserController::checkJwt($jwt);
        if ($jwt === null) {
            return new JsonResponse(
                [
                    'message' => 'Unauthorized',
                ],
                401,
            );
        }

        /** @var ?User */
        $user = Config::get()->entityManager()->getRepository(User::class)->find($jwt->userId);
        if ($user === null) {
            return new JsonResponse(
                [
                    'message' => 'Unauthorized',
                ],
                401,
            );
        }

        $body = json_decode($request->getBody()->getContents());

        $accountId = $body->id;
        /** @var ?Account */
        $account = Config::get()->entityManager()->getRepository(Account::class)->find($accountId);
        if ($account === null) {
            return new JsonResponse(
                [
                    'message' => 'Account not found',
                ],
                404,
            );
        }

        if ($account->getUser()->getId() !== $user->getId()) {
            return new JsonResponse(
                [
                    'message' => 'Unauthorized',
                ],
                401,
            );
        }

        if (isset($body->name)) {
            $account->setName($body->name);
        }
        Config::get()->entityManager()->flush();

        return new JsonResponse(
            [
                'id' => $account->getId(),
                'name' => $account->getName(),
                'balance' => $account->getBalance(),
            ],
        );
    }

    #[Route('/account', 'DELETE')]
    public static function delete(ServerRequest $request): Response
    {
        $jwt = $request->getHeader('Authorization')[0] ?? '';
        $jwt = str_replace('Bearer ', '', $jwt);
        $jwt = UserController::checkJwt($jwt);
        if ($jwt === null) {
            return new JsonResponse(
                [
                    'message' => 'Unauthorized',
                ],
                401,
            );
        }

        /** @var ?User */
        $user = Config::get()->entityManager()->getRepository(User::class)->find($jwt->userId);
        if ($user === null) {
            return new JsonResponse(
                [
                    'message' => 'Unauthorized',
                ],
                401,
            );
        }

        $accountId = $request->getQueryParams()['id'];
        /** @var ?Account */
        $account = Config::get()->entityManager()->getRepository(Account::class)->find($accountId);
        if ($account === null) {
            return new JsonResponse(
                [
                    'message' => 'Account not found',
                ],
                404,
            );
        }

        if ($account->getUser()->getId() !== $user->getId()) {
            return new JsonResponse(
                [
                    'message' => 'Unauthorized',
                ],
                401,
            );
        }

        if ($account->getBalance() !== 0.0) {
            return new JsonResponse(
                [
                    'message' => 'Account balance must be empty',
                ],
                400,
            );
        }

        Config::get()->entityManager()->remove($account);
        Config::get()->entityManager()->flush();

        return new JsonResponse(
            [
                'message' => 'Account deleted',
            ],
            200,
        );
    }
}
