<?php

declare(strict_types=1);

namespace App\Controller;

use App\BaseController;
use App\Attribute\Route;
use App\Model\Account;
use App\Model\User;
use App\ORM;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\TextResponse;
use Laminas\Diactoros\ServerRequest;

class TestController extends BaseController
{
    #[Route('/create', 'GET')]
    public static function create(ServerRequest $request): Response
    {
        $user = new User(
            name: 'John Doe',
            email: 'john@doe.com',
            password: '1234',
        );
        ORM::insert($user);

        $account1 = new Account(
            name: 'John Doe 1',
            user: $user,
            balance: 100,
        );
        ORM::insert($account1);

        $account2 = new Account(
            name: 'John Doe 2',
            user: $user,
            balance: 200,
        );
        ORM::insert($account2);

        return new Response(status: 201);
    }

    #[Route('/read', 'GET')]
    public static function read(ServerRequest $request): Response
    {
        $user = ORM::find(User::class)[0];
        $accounts = ORM::find(Account::class, ['user' => $user]);

        print_r($user);
        print_r($accounts);

        return new Response(headers: ['Content-Type' => 'text/plain']);
    }
}
