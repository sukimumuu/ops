<?php

test('the about page is available', function () {
    $this->get(route('about'))
        ->assertOk()
        ->assertSee('Lebih Dekat dengan Mihom')
        ->assertSee('Yang Menjadi Pegangan Kami');
});

test('the contact page is available', function () {
    $this->get(route('contact'))
        ->assertOk()
        ->assertSee('Mari Bicara tentang Properti Anda')
        ->assertSee('Tinggalkan Pesan Anda')
        ->assertSee('Pertanyaan Umum');
});
