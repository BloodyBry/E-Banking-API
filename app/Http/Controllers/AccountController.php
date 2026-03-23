<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateAccountRequest;
use App\Services\AccountService;
use Illuminate\Http\JsonResponse;

class AccountController extends Controller
{
    public function __construct(private AccountService $accountService)
    {
    }

    public function store(CreateAccountRequest $request): JsonResponse
    {
        $account = $this->accountService->createAccount(
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'message' => 'Compte créé avec succès.',
            'data' => $account,
        ], 201);
    }
}