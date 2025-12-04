<?php

namespace Database\Seeders;

use App\Models\Slot;
use Illuminate\Database\Seeder;

class SlotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Slot::exists()) {
            return;
        }

        Slot::create([
            'capacity' => 10,
            'remaining' => 1,
        ]);
    }
}
