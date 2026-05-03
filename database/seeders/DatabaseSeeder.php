<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Module;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create instructor
        $instructor = User::factory()->instructor()->create([
            'name' => 'Andra Saputra',
            'email' => 'andra@biter.test',
        ]);

        // Create a student
        $student = User::factory()->create([
            'name' => 'Mahasiswa Demo',
            'email' => 'student@biter.test',
        ]);

        // Create admin
        User::factory()->admin()->create([
            'name' => 'Admin BITER',
            'email' => 'admin@biter.test',
            'password' => bcrypt('password'),
        ]);
    }
}
