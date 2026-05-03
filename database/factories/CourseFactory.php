<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'instructor_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'tagline' => fake()->sentence(),
            'description' => fake()->paragraphs(3, true),
            'category' => fake()->randomElement(['Backend', 'Frontend', 'Full-stack', 'Testing', 'DevOps']),
            'level' => fake()->randomElement(['Beginner', 'Intermediate', 'Advanced']),
            'cover_url' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=800&q=80',
            'price' => 0,
            'status' => 'draft',
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function free(): static
    {
        return $this->state(fn () => [
            'price' => 0,
        ]);
    }

    public function paid(int $price = 29000): static
    {
        return $this->state(fn () => [
            'price' => $price,
        ]);
    }
}
