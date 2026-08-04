<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContractResource;
use App\Models\Contract;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ContractController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $contracts = Contract::where('is_active', true)->get();

        return ContractResource::collection($contracts);
    }

    public function show(string $slug): ContractResource
    {
        $contract = Contract::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return new ContractResource($contract);
    }
}