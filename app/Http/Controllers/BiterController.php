<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BiterController extends Controller
{
    public function welcome(): Response
    {
        return Inertia::render('Biter/Welcome', [
            'modules' => $this->modules(),
            'assessment' => $this->assessment(),
            'learningModel' => $this->learningModel(),
        ]);
    }

    public function about(): Response
    {
        return Inertia::render('Biter/About', [
            'researchers' => $this->researchers(),
            'researchPartners' => $this->researchPartners(),
        ]);
    }

    public function contact(): Response
    {
        return Inertia::render('Biter/Contact', [
            'researchers' => $this->researchers(),
            'subjects' => [
                'Informasi Umum',
                'Kerja Sama Penelitian',
                'Dukungan Teknis',
                'Lainnya',
            ],
        ]);
    }

    public function sendContact(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'institution' => 'nullable|string|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        // Mock: tidak benar-benar kirim email, hanya flash message.
        return back()->with('success', 'Pesan Anda telah terkirim. Tim peneliti akan menghubungi Anda segera.');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function modules(): array
    {
        return [
            [
                'number' => 'Modul I',
                'title' => 'Fondasi & Ideasi',
                'icon' => 'Lightbulb',
                'items' => [
                    'Pertemuan 1: Orientasi & Kontrak Belajar',
                    'Pertemuan 2: Konsep Dasar Kewirausahaan',
                    'Pertemuan 3: Kreativitas & Inovasi dalam Bisnis',
                    'Pertemuan 4: Analisis Peluang Usaha',
                    'Pertemuan 5: Produk Kreatif & Nilai Jual',
                ],
            ],
            [
                'number' => 'Modul II',
                'title' => 'Perancangan Model',
                'icon' => 'LayoutGrid',
                'items' => [
                    'Pertemuan 6: Pengenalan Business Model Canvas (BMC)',
                    'Pertemuan 7: Pengembangan BMC',
                    'Pertemuan 9: Pemasaran & Branding',
                    'Pertemuan 10: Harga & Analisis Biaya',
                    'Agenda Khusus: UTS',
                ],
            ],
            [
                'number' => 'Modul III',
                'title' => 'Eksekusi & Evaluasi',
                'icon' => 'Target',
                'items' => [
                    'Pertemuan 11: Etika & Karakter Wirausaha',
                    'Pertemuan 12: Penyusunan Business Plan',
                    'Pertemuan 13: Presentasi Ide Usaha (Pitching)',
                    'Pertemuan 14: Umpan Balik & Penyempurnaan',
                    'Pertemuan 15: Evaluasi Proyek Final',
                ],
            ],
            [
                'number' => 'Penilaian',
                'title' => 'Sistem & Capaian',
                'icon' => 'Award',
                'items' => [
                    'Presensi & Kehadiran: 10%',
                    'Tugas Individu: 20%',
                    'Tugas Kelompok: 20%',
                    'UTS: 25% | UAS: 25%',
                    'Capaian: Merancang model bisnis kriya berbasis potensi lokal.',
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<string, string|int>>
     */
    private function assessment(): array
    {
        return [
            ['label' => 'Presensi & Kehadiran', 'value' => 10],
            ['label' => 'Tugas Individu', 'value' => 20],
            ['label' => 'Tugas Kelompok', 'value' => 20],
            ['label' => 'UTS', 'value' => 25],
            ['label' => 'UAS', 'value' => 25],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function learningModel(): array
    {
        return [
            [
                'phase' => 'Pra-Kelas',
                'subtitle' => 'Luar Kelas',
                'icon' => 'BookOpen',
                'description' => 'Akses video materi dan e-modul secara mandiri sebelum tatap muka untuk membangun fondasi pemahaman.',
            ],
            [
                'phase' => 'Dalam Kelas',
                'subtitle' => 'Interaction',
                'icon' => 'Users',
                'description' => 'Aktivitas diskusi kolaboratif dan pemecahan masalah berbasis HOTS bersama dosen dan rekan sejawat.',
            ],
            [
                'phase' => 'Setelah Kelas',
                'subtitle' => 'Consolidation',
                'icon' => 'Sparkles',
                'description' => 'Refleksi metakognitif dan penyempurnaan produk belajar atau bisnis sebagai output nyata.',
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function researchers(): array
    {
        return [
            [
                'type' => 'lead',
                'name' => 'Andra Saputra, M.Pd.',
                'role' => 'Ketua Tim Peneliti',
                'institution' => 'ISI Padangpanjang',
                'expertise' => 'Teknologi Pendidikan, Kewirausahaan Kreatif, Pengembangan Media Pembelajaran Seni.',
                'email' => 'andra.saputra@isi-padangpanjang.ac.id',
                'department' => 'Program Studi Pendidikan Kriya, FSRD',
                'photo' => '/ketua.jpeg',
                'photo_alt' => 'Foto Andra Saputra',
                'photo_status' => 'ready',
            ],
            [
                'type' => 'researcher',
                'name' => 'Khairunnisa, M.Kom',
                'role' => 'Peneliti',
                'institution' => 'ISI Padangpanjang',
                'expertise' => 'Desain Komunikasi Visual, media digital, dan komunikasi visual untuk pengembangan pembelajaran.',
                'email' => 'khairunnisa@isi-padangpanjang.ac.id',
                'department' => 'Prodi Desain Komunikasi Visual',
                'photo' => '/peneliti-1.jpeg',
                'photo_alt' => 'Foto Khairunnisa',
                'photo_status' => 'ready',
            ],
        ];
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    private function researchPartners(): array
    {
        return [
            [
                'name' => 'Prof. Dr. Alwen Bentri, M.Pd',
                'title' => 'Dosen Teknologi Pendidikan, Universitas Negeri Padang',
                'photo' => '/alwen.png',
                'photo_alt' => 'Foto Prof. Dr. Alwen Bentri',
            ],
            [
                'name' => 'Prof. Dr. Abna Hidayati, M.Pd',
                'title' => 'Dosen Teknologi Pendidikan, Universitas Negeri Padang',
                'photo' => '/abna.png',
                'photo_alt' => 'Foto Prof. Dr. Abna Hidayati',
            ],
        ];
    }
}
