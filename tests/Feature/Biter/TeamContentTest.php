<?php

test('about page shows updated researcher and research partners', function () {
    $this->get('/biter/tentang')
        ->assertSuccessful()
        ->assertSee('Khairunnisa, M.Kom', false)
        ->assertSee('Prodi Desain Komunikasi Visual', false)
        ->assertSee('Asmidar, M.Sn', false)
        ->assertSee('Dosen Pendidikan Kriya', false)
        ->assertSeeInOrder([
            'Prof. Dr. Alwen Bentri, M.Pd',
            'Prof. Dr. Abna Hidayati, M.Pd',
        ], false)
        ->assertSee('Dosen Teknologi Pendidikan, Universitas Negeri Padang', false)
        ->assertSee('Prof. Dr. Abna Hidayati, M.Pd', false)
        ->assertSee('Prof. Dr. Alwen Bentri, M.Pd', false);
});

test('contact page shows added researcher profile', function () {
    $this->get('/biter/kontak')
        ->assertSuccessful()
        ->assertSee('Khairunnisa, M.Kom', false)
        ->assertSee('Asmidar, M.Sn', false)
        ->assertSee('Dosen Pendidikan Kriya', false)
        ->assertSee('Prodi Desain Komunikasi Visual', false)
        ->assertSee('ISI Padangpanjang', false);
});
