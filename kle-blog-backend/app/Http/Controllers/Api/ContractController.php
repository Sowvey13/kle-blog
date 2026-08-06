<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use Illuminate\Http\JsonResponse;

class ContractController extends Controller
{
    public function index(): JsonResponse
    {
        $contracts = Contract::where('is_active', true)->get();

        return response()->json([
            'data' => $contracts,
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $contract = Contract::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return response()->json([
            'data' => $contract,
        ]);
    }
}