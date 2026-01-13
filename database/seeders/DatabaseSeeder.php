<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed one event with three ticket types
        $events = \App\Models\Event::factory()->count(5)->hasTicketTypes(2)->create();
    }
}
