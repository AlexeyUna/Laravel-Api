<?php

namespace App\Services;

use App\Models\Hold;
use App\Models\Slot;

class SlotsService
{
    public function __construct(
        private readonly SlotsCache  $slotsCache,
        private readonly HoldService $holdService
    )
    {

    }

    public function getAvailableSlots($cursor = 0, $perPage = 10)
    {
        return $this->slotsCache->getAvailableSlots($cursor, $perPage);
    }

    public function createHold(Slot $slot, string $idempotencyKey): Hold
    {
        return $this->holdService->create($slot, $idempotencyKey);
    }

    public function confirmHold(Hold $hold): Hold
    {
        return $this->holdService->confirm($hold);
    }

    public function cancelHold(Hold $hold): Hold
    {
        return $this->holdService->cancel($hold);
    }
}
