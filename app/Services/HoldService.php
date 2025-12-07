<?php

namespace App\Services;

use App\Exceptions\HoldStatusException;
use App\Exceptions\NoSlotsAvailableException;
use App\Models\Hold;
use App\Models\Slot;
use Illuminate\Support\Facades\DB;

class HoldService
{
    public function __construct(
        private readonly SlotsCache $slotsCache)
    {
    }

    private function validateExistingHold(Hold $hold): Hold{
        return match ($hold->status) {
            'confirmed', 'cancelled' => throw new HoldStatusException(
                "This hold has '{$hold->status}' status. Use new Idempotency key to create hold."
            ),
            'held' => $hold->expires_at?->isPast()
                ? throw new HoldStatusException(
                    "Idempotency key is associated with an expired hold. Please use a new key."
                )
                : $hold,
            default => throw new HoldStatusException("Unhandled hold status: '{$hold->status}'"),
        };
    }
    public function create(Slot $slot, string $idempotencyKey): Hold
    {
        $existingHold = Hold::query()->where('idempotency_key', $idempotencyKey)->first();
        if ($existingHold) {
            return $this->validateExistingHold($existingHold);
        }

         DB::transaction(function () use ($slot, $idempotencyKey) {
                $slotForCheck = Slot::lockForUpdate()->findOrFail($slot->id);
                if ($slotForCheck->remaining <= 0) {
                    throw new NoSlotsAvailableException('No slots available');
                }

                Hold::query()->insertOrIgnore([
                    'idempotency_key' => $idempotencyKey,
                    'slot_id'         => $slot->id,
                    'status'          => 'held',
                    'expires_at'      => now()->addMinutes(5),
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);

         }, 3);

        return Hold::query()->where('idempotency_key', $idempotencyKey)->firstOrFail();
    }

    public function confirm(Hold $hold): Hold
    {
        if ($hold->status === 'confirmed') {
            return $hold;
        }

        if ($hold->status !== 'held') {
            throw new HoldStatusException(
                "Only 'held' holds can be confirmed. Current status: {$hold->status}"
            );
        }

        if ($hold->expires_at?->isPast()) {
            throw new HoldStatusException("Expired hold can not be confirmed");
        }

        DB::transaction(function () use ($hold) {
            $updated = Slot::where('id', $hold->slot_id)
                ->where('remaining', '>', 0)
                ->decrement('remaining');

            if ($updated === 0) {
                throw new NoSlotsAvailableException("No slots available for this hold");
            }

            $hold->update([
                'status' => 'confirmed',
                'expires_at' => null
            ]);

            $this->slotsCache->invalidateCache();
        });

        return $hold;
    }

    public function cancel(Hold $hold): Hold
    {
        if ($hold->status === 'cancelled')
            return $hold;

        DB::transaction(function () use ($hold) {
            if ($hold->status === 'confirmed') {
                $slot = Slot::lockForUpdate()->findOrFail($hold->slot_id);
                $slot->increment('remaining');
                $this->slotsCache->invalidateCache();
            }

            $hold->update([
                'status' => 'cancelled'
            ]);
        });

        return $hold;
    }
}
