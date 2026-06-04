<?php

namespace Database\Factories;

use App\Models\Series;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * @extends Factory<Series>
 */
class SeriesFactory extends Factory
{
    private static int $hierarchyIndex = 1;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => Str::uuid(),
            'user_id' => 1,
            'hierarchy_index' => self::$hierarchyIndex++,
            'title' => fake()->word(),
            'summary' => fake()->paragraph(),
            'episodes' => fake()->numberBetween(1, 12),
            'picture' => UploadedFile::fake()->image('picture.jpg')->getContent(),
            'seen' => false,
        ];
    }
}
