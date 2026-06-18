# Implementation Plan Redesign Exam Editor

## Tujuan

Dokumen ini memecah redesign editor exam menjadi langkah implementasi yang konkret untuk codebase BITTER saat ini. Fokusnya adalah:

- menjaga risiko rollout tetap rendah,
- memanfaatkan route dan model yang sudah ada,
- memecah pekerjaan backend dan frontend secara bertahap,
- memastikan autosave baru, struktur tab baru, dan publish readiness bisa diimplementasikan tanpa rewrite total.

Dokumen desain produk dan wireframe yang menjadi dasar plan ini ada di [EXAM_EDITOR_REDESIGN.md](file:///home/firdausriawan2/Projects/Personal/BITTER/EXAM_EDITOR_REDESIGN.md).

## Kondisi Existing

Implementasi sekarang berpusat di:

- [InstructorExamController.php](file:///home/firdausriawan2/Projects/Personal/BITTER/app/Http/Controllers/InstructorExamController.php)
- [ExamEditor.svelte](file:///home/firdausriawan2/Projects/Personal/BITTER/resources/js/pages/Instructor/ExamEditor.svelte)
- [web.php](file:///home/firdausriawan2/Projects/Personal/BITTER/routes/web.php)
- [InstructorExamPublishTest.php](file:///home/firdausriawan2/Projects/Personal/BITTER/tests/Feature/InstructorExamPublishTest.php)

### Fakta Teknis Saat Ini

- Route instruktur untuk editor exam sudah ada dan cukup lengkap untuk CRUD dasar soal.
- Page frontend masih satu file besar dan mengelola:
  - exam settings,
  - add question,
  - update question,
  - reorder question,
  - publish toggle,
  - recent attempts.
- Controller masih menangani:
  - build props Inertia,
  - validasi exam settings,
  - CRUD question,
  - publish validation,
  - attempts summary,
  - grading flow.
- Publish validation backend sudah ada, tetapi masih berbentuk exception sederhana per kasus.

## Strategi Implementasi

Redesign tidak disarankan dilakukan sekaligus. Jalur paling aman adalah:

1. stabilkan kontrak data editor,
2. pecah UI menjadi komponen dan tab,
3. refactor autosave,
4. pindahkan attempts ke tab terpisah,
5. rapikan publish readiness dan validasi,
6. baru tambah enhancement seperti duplicate atau section jika masih dibutuhkan.

## Prinsip Implementasi

- Pertahankan route utama editor yang sekarang agar migrasi ringan.
- Gunakan feature flag untuk editor baru jika rollout bertahap diperlukan.
- Jangan ubah flow student exam dalam fase ini.
- Pisahkan concern backend menjadi request validation, prop building, dan publish readiness.
- Di frontend, pecah page besar menjadi komponen kecil sebelum mengubah terlalu banyak behavior.
- Gunakan Wayfinder untuk semua koneksi frontend-backend seperti yang sudah dipakai sekarang.

## Target Arsitektur

### Backend

- `InstructorExamController` menjadi lebih tipis.
- Validasi request dipindahkan ke `FormRequest`.
- Persiapan props editor dipindahkan ke action atau presenter.
- Publish readiness dipusatkan dalam satu service/action yang dapat dipakai:
  - saat render editor,
  - saat preview,
  - saat publish.

### Frontend

- `ExamEditor.svelte` menjadi page shell.
- Tiap area editor dipecah menjadi komponen:
  - header,
  - tabs,
  - overview form,
  - builder layout,
  - preview panel,
  - attempts panel,
  - save state UI.
- State autosave dipisah:
  - exam overview state,
  - per-question edit state,
  - global save state.

## Fase Implementasi

## Phase 0 - Safety Rails

### Tujuan

Menyiapkan jalur rollout aman agar editor baru bisa diuji tanpa memutus flow existing.

### Pekerjaan

- Tambahkan feature flag `FEATURE_EXAM_EDITOR_V2`.
- Masukkan flag ke `config/features.php`.
- Share flag ke frontend lewat `HandleInertiaRequests.php` bila memang pola feature flag project saat ini sama seperti fitur Earnings.
- Tentukan fallback:
  - editor lama tetap bisa dipakai selama rollout,
  - editor baru aktif hanya jika flag aktif.

### File yang Tersentuh

- `config/features.php`
- `app/Http/Middleware/HandleInertiaRequests.php`
- [InstructorExamController.php](file:///home/firdausriawan2/Projects/Personal/BITTER/app/Http/Controllers/InstructorExamController.php)

### Output

- Editor baru dapat diaktifkan per environment tanpa mengubah route publik.

## Phase 1 - Stabilize Backend Contract

### Tujuan

Membuat backend siap melayani UI baru tanpa langsung mengganti seluruh frontend.

### Pekerjaan

- Ekstrak validasi request dari controller ke Form Request:
  - `UpsertCourseExamRequest`
  - `StoreExamQuestionRequest`
  - `UpdateExamQuestionRequest`
  - `ReorderExamQuestionsRequest`
  - `GradeExamAttemptRequest`
- Ekstrak publish readiness logic dari `ensureExamCanBePublished()` ke action terpisah.
- Buat action atau presenter untuk menyusun payload editor.
- Samakan format tanggal attempts ke ISO string, bukan `diffForHumans()`, agar UI baru lebih fleksibel.
- Tambahkan payload readiness di response editor, misalnya:
  - `readiness.blockers`
  - `readiness.warnings`
  - `readiness.summary`
- Tambahkan payload UI context:
  - `active_tab`
  - `last_saved_at`
  - `features.examEditorV2`

### Rekomendasi Class Baru

- `app/Actions/Exams/BuildInstructorExamEditorData.php`
- `app/Actions/Exams/ComputeExamPublishReadiness.php`
- `app/Http/Requests/Instructor/UpsertCourseExamRequest.php`
- `app/Http/Requests/Instructor/StoreExamQuestionRequest.php`
- `app/Http/Requests/Instructor/UpdateExamQuestionRequest.php`
- `app/Http/Requests/Instructor/ReorderExamQuestionsRequest.php`
- `app/Http/Requests/Instructor/GradeExamAttemptRequest.php`

### Catatan Implementasi

- Jangan mengubah nama route jika belum perlu.
- Pertahankan `courses.exam.edit`, `courses.exam.upsert`, `courses.exam.questions.*`, dan `courses.exam.attempts.show`.
- Jika perlu tab via URL, gunakan query string dulu seperti `?tab=builder`, bukan route baru.

### File Existing yang Direfaktor

- [InstructorExamController.php](file:///home/firdausriawan2/Projects/Personal/BITTER/app/Http/Controllers/InstructorExamController.php)
- [web.php](file:///home/firdausriawan2/Projects/Personal/BITTER/routes/web.php)

### Definition of Done

- Controller jauh lebih tipis.
- Readiness dapat dihitung di satu tempat.
- Payload editor siap dipakai UI bertab.

## Phase 2 - Restructure Frontend Shell

### Tujuan

Mengubah satu page besar menjadi shell editor bertab tanpa langsung menyentuh semua detail perilaku.

### Pekerjaan

- Ubah `ExamEditor.svelte` menjadi page shell.
- Tambahkan header editor baru:
  - exam title,
  - global save status,
  - tombol preview,
  - tombol publish.
- Tambahkan tab utama:
  - `Overview`
  - `Builder`
  - `Preview`
  - `Attempts`
- Pisahkan render per tab ke komponen terpisah.
- Pertahankan semua action backend existing agar migrasi tetap ringan.

### Rekomendasi Komponen Baru

- `resources/js/components/exam-editor/ExamEditorHeader.svelte`
- `resources/js/components/exam-editor/ExamEditorTabs.svelte`
- `resources/js/components/exam-editor/SaveStatusBadge.svelte`
- `resources/js/components/exam-editor/OverviewTab.svelte`
- `resources/js/components/exam-editor/BuilderTab.svelte`
- `resources/js/components/exam-editor/PreviewTab.svelte`
- `resources/js/components/exam-editor/AttemptsTab.svelte`

### Catatan Implementasi

- Gunakan runes Svelte 5 seperti existing code.
- Gunakan Wayfinder import untuk semua route action.
- Tab state awal bisa dibaca dari query string agar shareable dan browser back tetap natural.
- Attempts boleh tetap dikirim dari server di fase ini, lalu dioptimalkan di fase berikutnya.

### File yang Tersentuh

- [ExamEditor.svelte](file:///home/firdausriawan2/Projects/Personal/BITTER/resources/js/pages/Instructor/ExamEditor.svelte)
- komponen baru di `resources/js/components/exam-editor/`

### Definition of Done

- UI sudah tidak lagi satu halaman panjang.
- Attempts tidak tampil di Builder.
- Publish tidak lagi bergantung pada checkbox tunggal di settings area.

## Phase 3 - Builder Refactor

### Tujuan

Menjadikan question authoring sebagai pusat pengalaman.

### Pekerjaan

- Ubah area questions menjadi layout 3 panel:
  - outline kiri,
  - canvas editor tengah,
  - inspector kanan.
- Tambahkan state `activeQuestionId`.
- Render hanya detail soal aktif di canvas agar beban visual berkurang.
- Pindahkan add-question menjadi drawer atau panel aksi.
- Tampilkan status tiap soal di outline:
  - complete,
  - warning,
  - incomplete.
- Pertahankan reorder terlebih dahulu dengan tombol `Up/Down`, lalu pertimbangkan drag handle setelah flow stabil.

### Rekomendasi Komponen Baru

- `resources/js/components/exam-editor/builder/QuestionOutline.svelte`
- `resources/js/components/exam-editor/builder/QuestionOutlineItem.svelte`
- `resources/js/components/exam-editor/builder/QuestionCanvas.svelte`
- `resources/js/components/exam-editor/builder/QuestionInspector.svelte`
- `resources/js/components/exam-editor/builder/AddQuestionPanel.svelte`
- `resources/js/components/exam-editor/builder/QuestionStatusPill.svelte`

### Catatan Implementasi

- Soal yang belum lengkap tetap boleh disimpan sebagai draft.
- Validasi per-soal dibedakan dari blocker publish.
- Jangan mengubah payload `Question` secara radikal bila tidak perlu; tambahkan field baru secara bertahap.

### Contract Tambahan yang Disarankan

Tambahkan field turunan di props frontend:

- `is_complete`
- `validation_messages`
- `can_publish`

Jika tidak ingin menambah payload backend dulu, field ini bisa dihitung sementara di frontend. Namun target akhirnya lebih baik dihitung konsisten di backend.

### Definition of Done

- Builder memiliki fokus satu soal aktif.
- Pengguna bisa berpindah soal tanpa scroll panjang.
- Status tiap soal dapat dipindai cepat dari outline.

## Phase 4 - Autosave Redesign

### Tujuan

Menghilangkan rasa autosave yang agresif dan menggantinya dengan status save yang tenang.

### Pekerjaan

- Pisahkan state save menjadi:
  - `overviewSaveState`
  - `questionSaveState[questionId]`
  - `globalSaveState`
- Naikkan debounce text-heavy autosave menjadi 8-12 detik.
- Gunakan save on blur untuk field tertentu jika lebih natural.
- Hapus toast sukses untuk autosave.
- Tampilkan status di header:
  - `Saving...`
  - `Saved 14:32`
  - `Save failed`
- Tampilkan retry inline jika save gagal.
- Pertahankan tombol `Save draft` manual.

### Struktur State yang Disarankan

- `activeTab`
- `activeQuestionId`
- `overviewDraft`
- `questionDrafts`
- `dirtyOverview`
- `dirtyQuestions`
- `savingOverview`
- `savingQuestions`
- `failedQuestions`
- `lastSavedAt`

### Catatan Implementasi

- Autosave manual dan autosave background harus berbagi helper yang sama agar konsisten.
- Jangan panggil `router.post` atau `router.put` bertubi-tubi saat user masih mengetik.
- Jika user pindah tab saat ada request gagal, baru tampilkan guard.
- Save reorder dan delete tetap boleh langsung.

### File yang Tersentuh

- [ExamEditor.svelte](file:///home/firdausriawan2/Projects/Personal/BITTER/resources/js/pages/Instructor/ExamEditor.svelte)
- komponen builder terkait
- mungkin util lokal seperti `resources/js/components/exam-editor/useExamEditorState.ts` jika memang pola util TS dipakai di project

### Definition of Done

- Tidak ada toast autosave sukses yang berulang.
- Save state bisa dipahami user tanpa gangguan.
- Save failure terlihat jelas dan dapat ditindaklanjuti.

## Phase 5 - Publish Readiness Flow

### Tujuan

Mengubah publish dari toggle sederhana menjadi flow review yang eksplisit.

### Pekerjaan

- Hapus checkbox publish dari Overview.
- Jadikan publish sebagai aksi di tab `Preview`.
- Tampilkan blocker dan warning di panel readiness.
- Beri CTA perbaikan:
  - `Fix in Builder`
  - `Back to Overview`
- Saat publish diklik:
  - trigger save draft tersisa,
  - re-fetch readiness server,
  - lanjut publish hanya jika blocker kosong.
- Pertahankan validasi backend yang keras sebagai lapisan terakhir.

### Perubahan Backend

- Endpoint `upsert` tetap bisa menerima `is_published`, tetapi frontend baru tidak lagi mengikat checkbox langsung.
- Bila perlu, tambahkan action khusus publish:
  - `POST /instructor/courses/{course}/exam/publish`
- Namun untuk rollout ringan, phase awal masih bisa memakai endpoint `upsert` dengan payload `is_published=true`.

### Rekomendasi

- Untuk jangka pendek, pakai endpoint existing.
- Untuk jangka menengah, buat endpoint publish terpisah agar intent lebih jelas dan validation flow lebih bersih.

### Definition of Done

- Publish hanya dilakukan dari flow review.
- User melihat blocker secara eksplisit sebelum publish.
- Frontend dan backend sepakat pada definisi readiness.

## Phase 6 - Attempts Decoupling

### Tujuan

Memisahkan monitoring attempts dari authoring dan mengurangi payload builder.

### Pekerjaan

- Pindahkan UI attempts ke tab tersendiri.
- Gunakan deferred props atau load hanya saat tab attempts dibuka.
- Tambahkan filter sederhana jika payload attempts makin besar.
- Pertahankan halaman detail attempt yang sekarang.

### Opsi Implementasi

#### Opsi A - Tetap Satu Route, Deferred Props

- `edit()` tetap merender page yang sama.
- `attempts` dibuat deferred atau dimuat saat diperlukan.

#### Opsi B - Route Terpisah untuk Attempts Tab

- Tambah route editor attempts summary.
- Tab berpindah antar Inertia visit.

### Rekomendasi

- Pilih Opsi A lebih dulu karena minim perubahan arsitektur.

### Definition of Done

- Builder tidak lagi diramaikan data attempts.
- Load editor terasa lebih ringan.

## Phase 7 - UX Enhancements Setelah Core Stabil

### Opsional Setelah V1 Stabil

- duplicate question,
- keyboard navigation next/previous question,
- section atau grouping,
- import from question bank,
- restore last active tab/question,
- drag-and-drop reorder,
- richer preview mode.

### Catatan

- Jangan masukkan enhancement ini ke milestone awal jika target utama masih reliability dan clarity.

## Perubahan Data Contract

## Payload `edit()` Existing

Saat ini page menerima:

- `course`
- `exam`
- `attempts`

## Payload `edit()` Target

Target minimal setelah redesign:

```php
[
    'course' => [...],
    'exam' => [
        'id' => ...,
        'title' => ...,
        'description' => ...,
        'duration_minutes' => ...,
        'max_attempts' => ...,
        'pass_score' => ...,
        'is_published' => ...,
        'questions' => [...],
    ],
    'readiness' => [
        'blockers' => [...],
        'warnings' => [...],
        'summary' => [
            'total_questions' => ...,
            'objective_count' => ...,
            'essay_count' => ...,
            'total_points' => ...,
        ],
    ],
    'ui' => [
        'active_tab' => 'builder',
        'features' => [
            'exam_editor_v2' => true,
        ],
    ],
    'attempts' => [...], // bisa deferred
]
```

## Mapping Per File

## Backend

### [InstructorExamController.php](file:///home/firdausriawan2/Projects/Personal/BITTER/app/Http/Controllers/InstructorExamController.php)

- tipiskan controller,
- pindahkan request validation,
- panggil action untuk build props,
- panggil action readiness saat render dan publish.

### [web.php](file:///home/firdausriawan2/Projects/Personal/BITTER/routes/web.php)

- pertahankan route existing,
- opsional tambah route khusus publish bila diperlukan di phase lanjutan.

### File Baru yang Disarankan

- `app/Actions/Exams/BuildInstructorExamEditorData.php`
- `app/Actions/Exams/ComputeExamPublishReadiness.php`
- `app/Http/Requests/Instructor/UpsertCourseExamRequest.php`
- `app/Http/Requests/Instructor/StoreExamQuestionRequest.php`
- `app/Http/Requests/Instructor/UpdateExamQuestionRequest.php`
- `app/Http/Requests/Instructor/ReorderExamQuestionsRequest.php`
- `app/Http/Requests/Instructor/GradeExamAttemptRequest.php`

## Frontend

### [ExamEditor.svelte](file:///home/firdausriawan2/Projects/Personal/BITTER/resources/js/pages/Instructor/ExamEditor.svelte)

- diubah menjadi shell dan orchestrator state,
- tidak lagi memuat seluruh detail UI dalam satu file.

### File Baru yang Disarankan

- `resources/js/components/exam-editor/ExamEditorHeader.svelte`
- `resources/js/components/exam-editor/ExamEditorTabs.svelte`
- `resources/js/components/exam-editor/SaveStatusBadge.svelte`
- `resources/js/components/exam-editor/OverviewTab.svelte`
- `resources/js/components/exam-editor/BuilderTab.svelte`
- `resources/js/components/exam-editor/PreviewTab.svelte`
- `resources/js/components/exam-editor/AttemptsTab.svelte`
- `resources/js/components/exam-editor/builder/QuestionOutline.svelte`
- `resources/js/components/exam-editor/builder/QuestionCanvas.svelte`
- `resources/js/components/exam-editor/builder/QuestionInspector.svelte`
- `resources/js/components/exam-editor/builder/AddQuestionPanel.svelte`

## Strategy State Frontend

### State Global

- `activeTab`
- `activeQuestionId`
- `globalSaveStatus`
- `lastSavedAt`

### State Overview

- `overviewForm`
- `overviewDirty`
- `overviewSaving`
- `overviewFailed`

### State Question

- `questionDrafts`
- `dirtyQuestions`
- `savingQuestions`
- `failedQuestions`

### State Readiness

- `readiness`
- `publishPending`
- `publishBlocked`

## Strategy Testing

## Backend Tests

### Pertahankan dan Perluas

- [InstructorExamPublishTest.php](file:///home/firdausriawan2/Projects/Personal/BITTER/tests/Feature/InstructorExamPublishTest.php)
- [ExamFeatureTest.php](file:///home/firdausriawan2/Projects/Personal/BITTER/tests/Feature/ExamFeatureTest.php)

### Tambahkan Test Baru

- `tests/Feature/InstructorExamEditorTest.php`

### Skenario yang Harus Ditutup

- instructor dapat membuka editor v2 saat flag aktif,
- payload editor mengandung `readiness` yang benar,
- attempts tidak wajib dimuat saat tab builder bila pakai deferred strategy,
- publish gagal jika blocker ada,
- publish berhasil jika blocker kosong,
- question update tetap aman hanya untuk question milik exam tersebut,
- reorder tetap konsisten,
- feature flag mem-fallback ke editor lama saat nonaktif.

## Frontend Verification

Karena perubahan utamanya pada UX dan state handling:

- lakukan smoke check manual untuk:
  - pindah tab,
  - tambah soal,
  - edit overview,
  - autosave idle,
  - save failure handling,
  - publish flow,
  - buka attempts tab.

Jika nanti project memiliki browser test yang memadai, flow ini cocok ditutup dengan browser test bertahap.

## Rencana Eksekusi yang Disarankan

## Milestone 1

- feature flag,
- form request,
- action readiness,
- page shell dengan tabs,
- attempts dipindah ke tab terpisah secara visual.

## Milestone 2

- builder 3 panel,
- active question state,
- outline status,
- save status global baru.

## Milestone 3

- publish readiness panel,
- publish flow eksplisit,
- attempts deferred loading.

## Milestone 4

- UX enhancements opsional seperti duplicate dan keyboard nav.

## Urutan Pengerjaan Harian yang Paling Aman

1. Refactor backend tanpa ubah UI.
2. Pecah `ExamEditor.svelte` menjadi komponen tanpa ubah behavior lama terlalu banyak.
3. Tambahkan tab shell.
4. Pindahkan attempts dari canvas utama.
5. Implement active question builder.
6. Refactor autosave.
7. Rapikan publish flow.
8. Tambahkan deferred attempts atau optimasi payload.

## Estimasi Risiko

### Risiko Tinggi

- refactor autosave karena rawan race condition,
- migrasi dari full-list editor ke active-question editor,
- menyamakan readiness frontend dan backend.

### Risiko Menengah

- split komponen besar,
- memindahkan attempts ke deferred loading,
- feature flag fallback.

### Risiko Rendah

- tab shell,
- save status UI,
- extract request validation.

## Rekomendasi Final

Jangan mulai dari redesign visual penuh dulu. Mulai dari kontrak data dan struktur shell, lalu pindah ke builder focus dan autosave. Untuk codebase ini, jalur implementasi paling aman adalah:

1. backend cleanup,
2. shell tabs,
3. builder focus,
4. autosave redesign,
5. publish readiness,
6. attempts decoupling.

Dengan urutan ini, kamu bisa menjaga aplikasi tetap stabil sambil mengurangi pain point paling besar secara bertahap.
