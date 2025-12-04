<?php

namespace App\Http\Controllers;

use App\Http\Requests\AvailRequest;
use App\Http\Resources\SlotResource;
use App\Services\SlotsService;

class AvailabilityController extends Controller
{
    public function __construct(
        private readonly SlotsService $slotsService
    )
    {
    }

    public function index(AvailRequest $request)
    {
        $validated = $request->validated();

        $cursor = $validated['cursor'] ?? 0;
        $perPage = $validated['per_page'] ?? config('cache.slots_per_page', 10);

        $data = $this->slotsService->getAvailableSlots($cursor, $perPage);
        return SlotResource::collection($data['items'])->additional([
            'meta' => $data['meta'] ?? [],
        ]);
    }
}
