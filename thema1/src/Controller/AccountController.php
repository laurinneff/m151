<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\Route;
use App\BaseController;
use App\Model\Account;
use App\Model\User;
use App\ORM;
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
        $user = ORM::findOne(User::class, ['id' => $jwt->userId]);
        if ($user === null) {
            return new JsonResponse(
                [
                    'message' => 'Unauthorized',
                ],
                401,
            );
        }

        /** @var Account[] */
        $accounts = ORM::find(Account::class, ['user' => $user]);
        if ($accounts === null) {
            return new JsonResponse(
                [
                    'message' => 'Account not found',
                ],
                404,
            );
        }

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
        $user = ORM::findOne(User::class, ['id' => $jwt->userId]);
        if ($user === null) {
            return new JsonResponse(
                [
                    'message' => 'Unauthorized',
                ],
                401,
            );
        }

        $body = json_decode($request->getBody()->getContents());
        $account = new Account(
            name: $body->name,
            balance: 0,
            user: $user,
        );
        ORM::insert($account);

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
        $user = ORM::findOne(User::class, ['id' => $jwt->userId]);
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
        $account = ORM::findOne(Account::class, ['id' => $accountId]);
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
        ORM::update($account);

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
        $user = ORM::findOne(User::class, ['id' => $jwt->userId]);
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
        $account = ORM::findOne(Account::class, ['id' => $accountId]);
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

        ORM::delete($account);

        return new JsonResponse(
            [
                'message' => 'Account deleted',
            ],
            200,
        );
    }
}
