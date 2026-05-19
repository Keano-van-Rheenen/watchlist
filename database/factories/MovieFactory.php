<?php

namespace Database\Factories;

use App\Models\Movie;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * @extends Factory<Movie>
 */
class MovieFactory extends Factory
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
            'duration' => fake()->time(),
            'picture' => UploadedFile::fake()->image('picture.jpg')->getContent(),
        ];
    }
}
