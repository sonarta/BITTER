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

        // Define courses
        $coursesData = [
            [
                'title' => 'Konsep Dasar Kewirausahaan',
                'tagline' => 'Pahami fondasi berpikir dan bertindak sebagai wirausahawan kreatif.',
                'description' => 'Materi pengantar tentang konsep kewirausahaan, kreativitas, dan inovasi dalam konteks bisnis kreatif berbasis potensi lokal.',
                'category' => 'Kewirausahaan',
                'level' => 'Beginner',
                'cover_url' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=800&q=80',
                'price' => 0,
                'modules' => [
                    [
                        'title' => 'Orientasi & Pengantar',
                        'lessons' => [
                            ['title' => 'Pertemuan 1: Orientasi & Kontrak Belajar', 'duration' => 720, 'preview' => true],
                            ['title' => 'Pertemuan 2: Konsep Dasar Kewirausahaan', 'duration' => 900, 'preview' => true],
                            ['title' => 'Pertemuan 3: Kreativitas & Inovasi dalam Bisnis', 'duration' => 840],
                        ],
                    ],
                    [
                        'title' => 'Analisis & Peluang',
                        'lessons' => [
                            ['title' => 'Pertemuan 4: Analisis Peluang Usaha', 'duration' => 780],
                            ['title' => 'Pertemuan 5: Produk Kreatif & Nilai Jual', 'duration' => 900],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Perancangan Model Bisnis',
                'tagline' => 'Kuasai Business Model Canvas untuk bisnis kreatif Anda.',
                'description' => 'Panduan lengkap merancang model bisnis menggunakan BMC, strategi pemasaran, branding, dan analisis biaya.',
                'category' => 'Bisnis',
                'level' => 'Intermediate',
                'cover_url' => 'https://images.unsplash.com/photo-1542831371-29b0f74f9713?w=800&q=80',
                'price' => 0,
                'modules' => [
                    [
                        'title' => 'Business Model Canvas',
                        'lessons' => [
                            ['title' => 'Pertemuan 6: Pengenalan BMC', 'duration' => 720, 'preview' => true],
                            ['title' => 'Pertemuan 7: Pengembangan BMC', 'duration' => 900],
                            ['title' => 'Pertemuan 9: Pemasaran & Branding', 'duration' => 840],
                        ],
                    ],
                    [
                        'title' => 'Harga & Evaluasi',
                        'lessons' => [
                            ['title' => 'Pertemuan 10: Harga & Analisis Biaya', 'duration' => 780],
                            ['title' => 'Agenda Khusus: UTS', 'duration' => 600],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Simulasi & Presentasi Bisnis',
                'tagline' => 'Praktikkan pitching bisnis dan finalisasi model usaha.',
                'description' => 'Tahap akhir meliputi simulasi usaha, pitching bisnis, dan evaluasi keseluruhan.',
                'category' => 'Praktik',
                'level' => 'Advanced',
                'cover_url' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=800&q=80',
                'price' => 0,
                'modules' => [
                    [
                        'title' => 'Simulasi Usaha',
                        'lessons' => [
                            ['title' => 'Pertemuan 11: Simulasi Usaha Kreatif', 'duration' => 900],
                            ['title' => 'Pertemuan 12: Iterasi & Perbaikan Model', 'duration' => 780],
                        ],
                    ],
                    [
                        'title' => 'Presentasi & Evaluasi',
                        'lessons' => [
                            ['title' => 'Pertemuan 13: Pitching & Presentasi Bisnis', 'duration' => 840],
                            ['title' => 'Pertemuan 14: Review & Refleksi', 'duration' => 720],
                            ['title' => 'Agenda Khusus: UAS', 'duration' => 600],
                        ],
                    ],
                ],
            ],
        ];

        $createdCourses = [];

        foreach ($coursesData as $courseData) {
            $course = Course::create([
                'instructor_id' => $instructor->id,
                'title' => $courseData['title'],
                'slug' => Str::slug($courseData['title']),
                'tagline' => $courseData['tagline'],
                'description' => $courseData['description'],
                'category' => $courseData['category'],
                'level' => $courseData['level'],
                'cover_url' => $courseData['cover_url'],
                'price' => $courseData['price'],
                'status' => 'published',
                'published_at' => now(),
            ]);

            $createdCourses[] = $course;

            foreach ($courseData['modules'] as $moduleIndex => $moduleData) {
                $module = Module::create([
                    'course_id' => $course->id,
                    'title' => $moduleData['title'],
                    'sort_order' => $moduleIndex,
                ]);

                foreach ($moduleData['lessons'] as $lessonIndex => $lessonData) {
                    Lesson::create([
                        'module_id' => $module->id,
                        'title' => $lessonData['title'],
                        'slug' => Str::slug($lessonData['title']),
                        'content' => 'Konten materi untuk '.$lessonData['title'].'. Silakan ikuti instruksi dan selesaikan tugas yang diberikan.',
                        'video_url' => 'https://storage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
                        'duration_seconds' => $lessonData['duration'],
                        'is_preview' => $lessonData['preview'] ?? false,
                        'sort_order' => $lessonIndex,
                    ]);
                }
            }
        }

        // Enroll student in first two courses
        foreach (array_slice($createdCourses, 0, 2) as $course) {
            Enrollment::create([
                'user_id' => $student->id,
                'course_id' => $course->id,
                'enrolled_at' => now()->subDays(7),
            ]);
        }

        // Mark some lessons as completed for the first course
        $firstCourse = $createdCourses[0];
        $lessonsToComplete = $firstCourse->lessons()->take(3)->get();

        foreach ($lessonsToComplete as $lesson) {
            LessonProgress::create([
                'user_id' => $student->id,
                'lesson_id' => $lesson->id,
                'completed_at' => now()->subDays(rand(1, 5)),
            ]);
        }
    }
}
