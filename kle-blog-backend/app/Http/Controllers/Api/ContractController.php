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
        return ContractResource::collection(
            Contract::query()->active()->orderBy('title')->get(),
        );
    }

    public function show(string $slug): ContractResource
    {
        $contract = Contract::query()
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();

        return new ContractResource($contract);
    }
}
