<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransferRequest;
use App\Services\TransferService;
use Exception;
use Illuminate\Http\JsonResponse;

class TransferController extends Controller
{
    public function __construct(private TransferService $transferService)
    {
    }

    public function store(TransferRequest $request): JsonResponse
    {
        try {
            $transfer = $this->transferService->createTransfer(
                $request->user(),
                $request->validated()
            );

            $statusCode = $transfer->status->value === 'FAILED' ? 422 : 201;

            return response()->json([
                'message' => $transfer->status->value === 'FAILED'
                    ? 'Virement échoué.'
                    : 'Virement effectué avec succès.',
                'data' => $transfer,
            ], $statusCode);

        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}