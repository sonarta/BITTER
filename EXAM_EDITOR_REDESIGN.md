# PRD + Wireframe Redesign Exam Editor

## Ringkasan

Dokumen ini merangkum proposal redesign untuk fitur editor exam di BITTER. Fokus utamanya adalah:

- mengurangi friksi saat membuat dan mengedit exam,
- menurunkan gangguan dari autosave,
- memisahkan konteks kerja authoring, review, dan monitoring,
- meningkatkan kejelasan status draft, validasi, dan publish.

Editor saat ini berpusat di `resources/js/pages/Instructor/ExamEditor.svelte` dan masih mencampur exam settings, question authoring, publish state, serta recent attempts dalam satu layar panjang.

## Latar Belakang

Feedback pengguna menunjukkan bahwa fitur ini terasa sulit dan tidak nyaman digunakan. Masalah yang paling menonjol adalah intervensi autosave yang terlalu agresif, serta alur kerja yang mencampur terlalu banyak konteks dalam satu halaman.

Audit cepat terhadap implementasi saat ini menunjukkan:

- autosave berjalan sekitar 1.2 detik setelah perubahan pada exam settings,
- autosave juga berjalan per-soal dengan jeda sekitar 1.2 detik,
- exam settings, add question, list question, dan recent attempts tampil bersamaan,
- status publish berada di area settings, padahal validasinya bergantung pada kualitas seluruh soal.

Akibatnya, pengguna mudah kehilangan fokus, kurang percaya pada status simpan, dan kesulitan meninjau readiness sebelum publish.

## Problem Statement

Editor exam saat ini memaksa pengguna mengelola metadata exam, menulis soal, memperbaiki validasi, dan mengambil keputusan publish dalam satu flow yang bercampur. Struktur ini meningkatkan beban kognitif, menyulitkan navigasi saat jumlah soal bertambah, dan membuat autosave terasa mengganggu alih-alih membantu.

## Goals

- Mengurangi waktu dari draft exam baru sampai publish-ready.
- Meningkatkan rasa kontrol dan kepercayaan pengguna terhadap proses simpan.
- Memisahkan konteks kerja setup, authoring, review, dan monitoring.
- Membuat validasi publish lebih jelas dan mudah ditindaklanjuti.
- Menjaga editor tetap responsif untuk exam dengan jumlah soal yang lebih banyak.

## Non Goals

- Tidak menambah collaborative real-time editing.
- Tidak merombak halaman pengerjaan exam untuk student.
- Tidak menambah workflow approval multi-step.
- Tidak menambah tipe soal baru pada fase awal redesign.

## Persona Utama

### 1. Instruktur / Dosen

- Ingin membuat exam dengan cepat.
- Lebih fokus ke isi soal daripada konfigurasi teknis.
- Butuh pengalaman edit yang aman dan tidak bikin cemas data hilang.

### 2. Admin Akademik / Operator

- Mengatur exam agar siap tayang dan sesuai aturan.
- Membutuhkan validasi, status readiness, dan publish flow yang jelas.

### 3. Reviewer Mata Kuliah

- Ingin memeriksa struktur dan kelengkapan exam dengan cepat.
- Lebih membutuhkan mode review yang mudah dipindai daripada mode edit penuh.

## Jobs To Be Done

- Saat saya membuat exam baru, saya ingin mengisi informasi dasar dulu agar kerangka exam jelas sebelum menulis soal.
- Saat saya menulis banyak soal, saya ingin fokus pada daftar dan isi soal tanpa terganggu oleh settings lain.
- Saat sistem menyimpan perubahan, saya ingin tahu apa yang aman tersimpan dan apa yang gagal.
- Saat saya akan publish, saya ingin melihat semua blocker dalam satu tempat agar bisa memperbaikinya dengan cepat.
- Saat saya kembali ke exam draft, saya ingin melanjutkan dari konteks terakhir tanpa harus scroll panjang.

## Pain Points Saat Ini

### Beban Kognitif Tinggi

Settings exam, editor soal, validasi publish, dan recent attempts tampil dalam satu halaman besar.

### Autosave Terasa Mengganggu

Autosave yang terlalu cepat membuat pengguna merasa sistem terlalu sering ikut campur saat mereka masih mengetik atau berpikir.

### Navigasi Sulit

Semakin banyak soal, semakin berat halaman untuk dipindai dan dijelajahi. Pengguna harus scroll panjang untuk berpindah antar bagian.

### Review Readiness Kurang Jelas

Masalah publish baru terasa di tengah settings, padahal sumber masalah sering berada pada daftar soal.

### Context Mixing

Recent attempts adalah konteks monitoring, bukan authoring, tetapi saat ini hadir di halaman yang sama.

## Prinsip Redesign

- Pisahkan alur kerja berdasarkan niat pengguna.
- Jadikan authoring soal sebagai pengalaman utama.
- Pertahankan autosave, tetapi ubah menjadi tenang dan dapat diprediksi.
- Tempatkan publish sebagai hasil dari review readiness, bukan toggle yang bercampur dengan drafting.
- Bedakan mode edit, preview, dan attempts agar tidak saling mengganggu.

## Arah Solusi

Redesign editor menjadi workspace bertahap dengan empat tab utama:

- `Overview`
- `Builder`
- `Preview`
- `Attempts`

### Overview

Berisi metadata exam level tinggi:

- title,
- description,
- availability,
- duration,
- max attempts,
- passing rules,
- exam instructions.

### Builder

Berisi pengalaman authoring soal yang terfokus:

- daftar soal di panel kiri,
- editor soal aktif di panel tengah,
- inspector atau properti kontekstual di panel kanan.

### Preview

Berisi:

- student preview,
- validation checklist,
- blocker dan warning,
- publish readiness summary.

### Attempts

Berisi:

- recent attempts,
- status pengerjaan,
- score ringkas,
- item insight bila diperlukan.

Recent attempts dipindahkan ke tab ini agar tidak mengganggu proses membuat soal.

## Information Architecture

### Top Level Navigation

- Overview
- Builder
- Preview
- Attempts

### Builder Layout

- panel kiri: question outline
- panel tengah: question editor
- panel kanan: inspector

### Publish Flow

- publish tidak lagi menjadi checkbox di settings,
- publish menjadi aksi eksplisit di area preview atau review,
- tombol publish aktif hanya jika blocker selesai.

## Detail Autosave Baru

### Masalah Autosave Lama

- terlalu cepat,
- terlalu sering,
- tidak cukup menjelaskan state simpan,
- rawan terasa menginterupsi fokus pengguna.

### Prinsip Autosave Baru

- draft lokal diperbarui instan,
- sinkronisasi ke server dilakukan dengan debounce yang lebih tenang,
- status simpan tampil halus di header, bukan toast sukses berulang,
- save failure harus terlihat jelas dan bisa di-retry,
- perubahan exam settings dan perubahan per-soal dipisah sebagai unit save yang berbeda.

### Rekomendasi Perilaku

- debounce text-heavy fields sekitar 8-12 detik setelah idle,
- save struktural seperti reorder, delete, move section dapat dilakukan langsung setelah aksi selesai,
- tampilkan status seperti `Saving...`, `Saved 14:32`, atau `Save failed`,
- sediakan tombol `Save draft` manual untuk memberi rasa kontrol,
- tampilkan navigation guard hanya jika sinkronisasi gagal atau ada perubahan yang benar-benar belum aman.

## Functional Requirements

### Exam Workspace

- Pengguna dapat berpindah antara Overview, Builder, Preview, dan Attempts tanpa kehilangan konteks.
- Sistem menyimpan step aktif dan konteks soal terakhir yang diedit.

### Overview

- Pengguna dapat membuat exam draft tanpa harus melengkapi semua field sejak awal.
- Pengguna dapat mengubah metadata utama exam secara terpisah dari authoring soal.

### Builder

- Pengguna dapat menambah soal baru.
- Pengguna dapat mengedit satu soal dengan fokus penuh.
- Pengguna dapat menghapus, menduplikasi, dan mengurutkan soal.
- Pengguna dapat melihat status kelengkapan setiap soal dari outline.

### Preview

- Sistem menampilkan validation checklist yang dibagi menjadi blocker dan warning.
- Pengguna dapat melihat student preview sebelum publish.
- Tombol publish hanya aktif jika blocker selesai.

### Attempts

- Pengguna dapat melihat recent attempts tanpa masuk ke halaman authoring.
- Attempts bersifat read-only secara default.

### Autosave

- Sistem menampilkan status save global.
- Sistem dapat menandai item tertentu jika save item tersebut gagal.
- Sistem tidak mengandalkan toast sukses per autosave.

## Non Functional Requirements

- Editor tetap responsif untuk exam dengan soal yang banyak.
- Save operation aman terhadap request yang tumpang tindih.
- Perubahan gagal simpan tidak boleh hilang secara diam-diam.
- Feedback lokal harus muncul cepat walaupun sinkronisasi server belum selesai.
- Implementasi harus tetap sesuai arsitektur Laravel + Inertia + Svelte yang sudah ada.

## Success Metrics

### Primary Metrics

- median time dari draft ke publish-ready,
- draft-to-publish conversion rate,
- autosave failure rate,
- publish failure rate karena blocker.

### Secondary Metrics

- waktu rata-rata menambah atau mengedit satu soal,
- jumlah complaint terkait autosave,
- jumlah sesi edit per exam sebelum publish,
- error rate pada action reorder, save, delete, dan publish.

### Target Awal

- turunkan waktu publish-ready setidaknya 25%,
- turunkan complaint atau insiden terkait autosave minimal 40%,
- turunkan save-related error minimal 50%,
- tingkatkan draft-to-publish conversion minimal 15%.

## Risks

- Pengguna lama mungkin perlu adaptasi terhadap flow baru.
- State management akan lebih kompleks karena save dipisah per domain data.
- Scope redesign bisa melebar ke bank soal, template, atau analytics jika tidak dijaga.

## Mitigasi

- Gunakan feature flag untuk rollout bertahap.
- Fokus fase awal pada struktur flow dan reliability, bukan fitur tambahan.
- Bedakan blocker dan warning agar publish rules tidak terlalu kaku.
- Sediakan fallback sementara bila diperlukan saat beta rollout.

## Rollout Plan

### Phase 0 - Discovery

- kumpulkan complaint yang paling sering,
- petakan flow lama,
- identifikasi momen friksi tertinggi.

### Phase 1 - Prototype Validation

- uji wireframe atau prototype ke beberapa pengguna aktif,
- validasi apakah pemisahan tab membantu.

### Phase 2 - MVP

- implementasi Overview, Builder, Preview, Attempts,
- implementasi autosave baru,
- implementasi validation checklist.

### Phase 3 - Controlled Rollout

- rilis dengan feature flag ke sebagian pengguna,
- ukur funnel dan save failures.

### Phase 4 - Full Launch

- jadikan editor baru sebagai default,
- lanjutkan iterasi dari data pemakaian dan feedback.

## Open Questions

- Apakah user lebih sering membuat exam dari nol atau clone dari exam lama?
- Apakah ada aturan publish tambahan selain validasi soal?
- Berapa volume soal yang dianggap kasus umum?
- Apakah section atau grouping soal perlu masuk fase awal?

---

## Wireframe Low Fidelity

Wireframe berikut memetakan struktur halaman baru secara tekstual. Fokusnya adalah memisahkan konteks kerja dan menjadikan editor soal sebagai pusat pengalaman.

### Screen 1 - Overview

```text
+----------------------------------------------------------------------------------+
| Exam Editor                                   Saved 2 min ago   Preview  Publish |
+----------------------------------------------------------------------------------+
| [Overview] [Builder] [Preview] [Attempts]                                       |
+----------------------------------------------------------------------------------+
| Exam Title: [ Midterm Biology 101____________________________________________ ] |
| Description: [_______________________________________________________________ ] |
|                                                                              [] |
|----------------------------------------------------------------------------------|
| Availability            | Rules & Timing              | Grading                |
| Start: [date][time]     | Duration: [ 90 min ]        | Total points: [ 100 ] |
| End:   [date][time]     | Attempts: [ 1 ]             | Passing: [ 60 ]       |
| Timezone: [ GMT+7    ]  | Shuffle: [on/off]           | Show results: [rule]  |
|                         | Access code: [_______]      |                       |
|----------------------------------------------------------------------------------|
| Instructions                                                                   |
| [____________________________________________________________________________]  |
| [____________________________________________________________________________]  |
|----------------------------------------------------------------------------------|
|                           [ Save Draft ]   [ Continue to Builder ]              |
+----------------------------------------------------------------------------------+
```

### Catatan

- Fokus hanya pada metadata exam.
- Tidak ada daftar soal di layar ini.
- Publish bukan aksi utama di tahap ini.

### Screen 2 - Builder Empty State

```text
+----------------------------------------------------------------------------------+
| Exam Editor                                   All changes saved   Preview Publish |
+----------------------------------------------------------------------------------+
| [Overview] [Builder] [Preview] [Attempts]                                       |
+----------------------------------------------------------------------------------+
| Questions (0)                         |                                           |
|---------------------------------------|-------------------------------------------|
| No questions yet                      |   Build your exam                         |
|                                       |   Start with one of these actions:        |
| [ + Add first question ]              |                                           |
| [ Import from question bank ]         |   [ + New Question ]                      |
| [ Create section ]                    |   [ Import Bank ]                         |
|                                       |   [ Create Section ]                      |
|                                       |                                           |
|                                       |   Tip: sections help organize long exams  |
+----------------------------------------------------------------------------------+
```

### Catatan

- Empty state harus memberi arah yang sangat jelas.
- Builder menjadi entry point utama setelah exam mulai berisi soal.

### Screen 3 - Builder Main Workspace

```text
+--------------------------------------------------------------------------------------------------+
| Exam Editor                                      Saving... / Saved      Preview   Publish        |
+--------------------------------------------------------------------------------------------------+
| [Overview] [Builder] [Preview] [Attempts]                                                       |
+--------------------------------------------------------------------------------------------------+
| Questions / Outline              | Question Editor                             | Inspector       |
|----------------------------------|---------------------------------------------|-----------------|
| Search [___________]             | Q12. Multiple Choice                        | Question Settings|
| + Add | Import | Section         |---------------------------------------------|-----------------|
|----------------------------------| Stem                                        | Points [ 5 ]    |
| Section A                        | [ What is the function of mitochondria?   ] | Required [x]    |
| 1. Intro MCQ          Complete   | [_________________________________________] | Shuffle opts [ ]|
| 2. Label diagram      Warning    |                                             | Difficulty [Med]|
| 3. Essay             Incomplete  | Choices                                     | Tags [_____]    |
|----------------------------------| (o) A. Energy production                    | Feedback [edit] |
| Section B                        | ( ) B. Protein synthesis                    |-----------------|
| 4. Case study         Complete   | ( ) C. Cell division                        | Actions         |
| 5. True/False        Incomplete  | ( ) D. Waste removal                        | Duplicate       |
|                                  |                                             | Move to section |
| [drag handle] reorder            | [ + Add choice ]                            | Delete          |
|                                  |---------------------------------------------|-----------------|
|                                  | [ Previous ] [ Save draft ] [ Next ]                          |
+--------------------------------------------------------------------------------------------------+
```

### Catatan

- Panel kiri untuk navigasi cepat dan scan status.
- Panel tengah fokus pada satu soal aktif.
- Panel kanan hanya untuk properti kontekstual.

### Screen 4 - Add Question Drawer

```text
+----------------------------------------------------------------------------------+
| Builder                                                           [ Close ]      |
+----------------------------------------------------------------------------------+
| Questions / Outline                | Main Canvas                 | Add Question  |
|------------------------------------|-----------------------------|----------------|
| Section A                          | Existing question view      | Choose type    |
| 1. Intro MCQ                       | remains visible dimmed      |----------------|
| 2. Label diagram                   |                             | ( ) Multiple   |
| 3. Essay                           |                             | ( ) True/False |
|                                    |                             | ( ) Essay      |
|                                    |                             | ( ) Matching   |
|                                    |                             | ( ) Short Ans  |
|                                    |                             |----------------|
|                                    |                             | More actions   |
|                                    |                             | [ Import bank ]|
|                                    |                             | [ Duplicate Q ]|
|                                    |                             | [ Create sect ]|
|                                    |                             |----------------|
|                                    |                             | [ Cancel ]     |
|                                    |                             | [ Add Question ]|
+----------------------------------------------------------------------------------+
```

### Catatan

- Menambah soal tidak perlu memecah fokus atau scroll ke bagian lain.
- Drawer menjaga konteks builder tetap stabil.

### Screen 5 - Preview and Validation

```text
+----------------------------------------------------------------------------------+
| Exam Editor                                   Saved               Builder Publish |
+----------------------------------------------------------------------------------+
| [Overview] [Builder] [Preview] [Attempts]                                       |
+----------------------------------------------------------------------------------+
| Validation Panel                     | Student Preview                            |
|--------------------------------------|--------------------------------------------|
| Errors (2)                           | Biology 101 Midterm                        |
| - Q5 has no correct answer           |--------------------------------------------|
| - End date is before start date      | 1. What is the function of mitochondria?   |
|                                      | ( ) Energy production                      |
| Warnings (3)                         | ( ) Protein synthesis                      |
| - Q2 has no image alt text           | ( ) Cell division                          |
| - 1 section has no instructions      | ( ) Waste removal                          |
| - Passing score not set              |                                            |
|                                      | [ Previous ]               [ Next ]        |
|--------------------------------------|--------------------------------------------|
| Readiness: Not ready to publish      |                                            |
| [ Fix in Builder ]   [ Back to Overview ]   [ Publish disabled ]                 |
+----------------------------------------------------------------------------------+
```

### Catatan

- Blocker dan warning dipisah jelas.
- Publish readiness menjadi eksplisit dan bisa ditindaklanjuti.

### Screen 6 - Attempts

```text
+----------------------------------------------------------------------------------+
| Exam Editor                                   Published            Preview        |
+----------------------------------------------------------------------------------+
| [Overview] [Builder] [Preview] [Attempts]                                       |
+----------------------------------------------------------------------------------+
| Attempts Summary                                                                  |
|----------------------------------------------------------------------------------|
| Avg Score        Completion Rate      Attempts Submitted     Needs Review         |
| 72%              84%                  126                    9                    |
|----------------------------------------------------------------------------------|
| Filters: [Date] [Status] [Section] [Search student___________________________]  |
|----------------------------------------------------------------------------------|
| Student Name     Started        Submitted      Score     Status      Review       |
| A. Putri         08:11          09:22          81        Completed   [Open]       |
| B. Hadi          08:15          -              -         In progress [Open]       |
| C. Lestari       08:20          09:10          65        Completed   [Open]       |
|----------------------------------------------------------------------------------|
| Item Insights                                                                     |
| Q1 92% correct   Q2 44% correct   Q3 68% correct   Q4 flagged ambiguity          |
+----------------------------------------------------------------------------------+
```

### Catatan

- Attempts dipisah dari builder.
- Konteks monitoring tidak lagi mengganggu authoring.

## State Penting

- empty exam,
- draft dirty,
- saving,
- saved,
- save failed,
- question incomplete,
- validation warning,
- validation blocker,
- reordering,
- delete confirmation,
- unsaved navigation guard.

## Interaction Notes

### Autosave

- simpan lokal instan per perubahan,
- sinkron server dilakukan saat user idle,
- jangan tampilkan toast sukses untuk autosave,
- tampilkan retry jika save gagal,
- simpan per domain data, bukan seluruh halaman sekaligus.

### Question Outline

- tampilkan status per soal: complete, warning, incomplete,
- gunakan drag handle untuk reorder,
- dukung collapse per section untuk exam panjang.

### Question Editor

- fokus ke satu soal aktif,
- sediakan previous dan next untuk authoring cepat,
- pindah soal tidak memaksa hilang konteks edit.

### Preview

- tampilkan checklist readiness,
- bedakan blocker dan warning,
- arahkan user kembali ke layar yang tepat saat memperbaiki masalah.

## Keputusan Kunci

- Ubah editor dari satu halaman panjang menjadi workspace bertahap.
- Pindahkan recent attempts ke tab terpisah.
- Jadikan builder sebagai inti authoring.
- Ubah publish dari checkbox menjadi aksi eksplisit.
- Ubah autosave menjadi ambient behavior yang tenang dan dapat dipercaya.

## Rekomendasi Implementasi Awal

Urutan implementasi yang disarankan:

1. Pecah struktur halaman menjadi Overview, Builder, Preview, Attempts.
2. Ubah publish checkbox menjadi flow review dan publish readiness.
3. Refactor authoring soal menjadi outline + active editor + inspector.
4. Ubah autosave menjadi per-domain state dengan feedback yang lebih tenang.
5. Pindahkan recent attempts keluar dari halaman builder utama.

## Lampiran Audit Singkat

Area yang paling berkontribusi pada friksi saat ini:

- autosave exam settings dan question editor sama-sama aktif terlalu cepat,
- validasi publish bercampur dengan form settings,
- daftar soal belum menjadi navigasi utama,
- recent attempts hadir di halaman authoring,
- pengguna harus berpindah fokus antara setup, edit, review, dan monitoring dalam satu layar.
