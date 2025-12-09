<?php

namespace App\Services;

use App\Models\Slot;
use Illuminate\Support\Facades\Cache;

class SlotsCache
{

    private mixed $cacheKey = null;

    public function __construct()
    {
        $this->cacheKey = config('cache.cache_tag');
    }

    public function getAvailableSlots(int $cursor = 0, int $perPage = 10)
    {
        $version = Cache::get("{$this->cacheKey}:availability:version", 1);
        $cacheKeyGroup = "{$this->cacheKey}:{$version}:{$cursor}:{$perPage}";
        $softTTL = config('cache.cache_ttl');
        return Cache::flexible(
            $cacheKeyGroup,
            [$softTTL, $softTTL * 3],
            callback: fn() => $this->loadPage($cursor, $perPage)
        );
    }

    private function loadPage(int $cursor, int $perPage): array
    {
        $query = Slot::query()
            ->select(['id', 'capacity', 'remaining'])
            ->where('remaining', '>', 0)
            ->orderBy('id');

        if ($cursor > 0) {
            $query->where('id', '>=', $cursor);
        }

        $items = $query->limit($perPage + 1)->get();
        $itemsCount = $items->count();

        $nextCursor = null;
        if ($itemsCount > $perPage) {
            $lastItem = $items->pop();
            $nextCursor = $lastItem->id;
        }

        return [
            'items' => $items,
            'meta' => [
                'count' => $items->count(),
                'cursor' => $cursor,
                'next_cursor' => $nextCursor
            ],
        ];
    }

    public function invalidateCache(): void
    {
        Cache::increment("{$this->cacheKey}:availability:version");
    }
}
