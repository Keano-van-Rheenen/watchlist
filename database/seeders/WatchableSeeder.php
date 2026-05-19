<?php

namespace Database\Seeders;

use App\Models\Movie;
use App\Models\Series;
use Database\Factories\MovieFactory;
use Database\Factories\SeriesFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WatchableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Movie::factory()->times(15)->create();
        Series::factory()->times(10)->create();
    }
}
