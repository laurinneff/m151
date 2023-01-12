<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\Route;
use App\BaseController;
use App\Model\Account;
use App\Model\Transaction;
use App\Model\User;
use App\ORM;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequest;

class TransactionController extends BaseController
{
    #[Route('/transaction', 'GET')]
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

        /** @var Transaction[] */
        $transactions = [];

        foreach ($accounts as $account) {
            $transactions[$account->getId()] = ORM::find(Transaction::class, ['accountFrom' => $account]);
            $transactions[$account->getId()] = array_merge($transactions[$account->getId()], ORM::find(Transaction::class, ['accountTo' => $account]));
            $transactions[$account->getId()] = array_map(
                fn (Transaction $transaction) =>
                    [
                        'id' => $transaction->getId(),
                        'amount' => $transaction->getAmount(),
                        'description' => $transaction->getDescription(),
                        'timestamp' => $transaction->getTimestamp(),
                        'accountFrom' => $transaction->getAccountFrom()->getId(),
                        'accountTo' => $transaction->getAccountTo()->getId(),
                    ],
                $transactions[$account->getId()]
            );
        }

        return new JsonResponse($transactions);
    }

    #[Route('/transaction', 'POST')]
    public static function createTransaction(ServerRequest $request): Response
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

        $accountFrom = ORM::findOne(Account::class, ['id' => $body->accountFrom]);
        $accountTo = ORM::findOne(Account::class, ['id' => $body->accountTo]);

        if ($accountFrom === null || $accountTo === null) {
            return new JsonResponse(
                [
                    'message' => 'Account not found',
                ],
                404,
            );
        }

        if ($accountFrom->getBalance() < $body->amount) {
            return new JsonResponse(
                [
                    'message' => 'Insufficient funds',
                ],
                400,
            );
        }

        $transaction = new Transaction(
            accountFrom: $accountFrom,
            accountTo: $accountTo,
            amount: $body->amount,
            description: $body->description,
        );

        ORM::insert($transaction);

        return new JsonResponse(
            [
                'message' => 'Transaction created',
            ],
            201,
        );
    }
}
