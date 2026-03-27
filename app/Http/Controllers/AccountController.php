<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateAccountRequest;
use App\Models\Account;
use App\Services\AccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function __construct(private AccountService $accountService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $accounts = $request->user()
            ->accounts()
            ->with('users')
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Liste des comptes récupérée avec succès.',
            'data' => $accounts,
        ]);
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

    public function show(Request $request, int $id): JsonResponse
    {
        $account = $request->user()
            ->accounts()
            ->with('users')
            ->find($id);

        if (! $account) {
            return response()->json([
                'message' => 'Compte introuvable ou accès non autorisé.'
            ], 404);
        }

        return response()->json([
            'message' => 'Détail du compte récupéré avec succès.',
            'data' => $account,
        ]);
    }
}