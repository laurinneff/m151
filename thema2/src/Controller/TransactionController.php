<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\Route;
use App\BaseController;
use App\Config;
use App\Model\Account;
use App\Model\Transaction;
use App\Model\User;
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

        /** @var Transaction[] */
        $transactions = [];
        
        foreach ($accounts as $account) {
            $transactions[$account->getId()] = $account->getTransactions();
            $transactions[$account->getId()] = array_map(
                fn (Transaction $transaction) =>
                    [
                        'id' => $transaction->getId(),
                        'amount' => $transaction->getAmount(),
                        'description' => $transaction->getDescription(),
                        'timestamp' => $transaction->getTimestamp()->format('c'),
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

        $accountFrom = Config::get()->entityManager()->getRepository(Account::class)->find($body->accountFrom);
        $accountTo = Config::get()->entityManager()->getRepository(Account::class)->find($body->accountTo);

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

        $transaction = new Transaction();
        $transaction->setAccountFrom($accountFrom);
        $transaction->setAccountTo($accountTo);
        $transaction->setAmount($body->amount);
        $transaction->setDescription($body->description);

        $accountFrom->setBalance($accountFrom->getBalance() - $body->amount);
        $accountTo->setBalance($accountTo->getBalance() + $body->amount);

        Config::get()->entityManager()->persist($transaction);
        Config::get()->entityManager()->flush();

        return new JsonResponse(
            [
                'message' => 'Transaction created',
            ],
            201,
        );
    }
}
