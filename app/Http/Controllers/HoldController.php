<?php

namespace App\Http\Controllers;

use App\Http\Requests\HoldRequest;
use App\Http\Resources\HoldResource;
use App\Models\Hold;
use App\Models\Slot;
use App\Services\SlotsService;
use Symfony\Component\HttpFoundation\Response;

class HoldController extends Controller
{
    public function __construct(
        private readonly SlotsService $slotsService
    ){}

    public function hold(HoldRequest $request, Slot $slot)
    {
        $hold = $this->slotsService->createHold($slot, $request->idempotencyKey());
        return HoldResource::make($hold)
            ->response()
            ->setStatusCode($hold->wasRecentlyCreated ?
                Response::HTTP_CREATED :
                Response::HTTP_OK);
    }

    public function confirm(Hold $hold)
    {
        $data = $this->slotsService->confirmHold($hold);
        return HoldResource::make($data);
    }

    public function delete(Hold $hold)
    {
        $data = $hold->status === 'cancelled' ? $hold : $this->slotsService->cancelHold($hold);
        return HoldResource::make($data);
    }
}
