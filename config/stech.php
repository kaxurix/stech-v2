<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Jadwal Pendaftaran & Pengumpulan Karya
    |--------------------------------------------------------------------------
    |
    | Mengacu timeline resmi: "Pendaftaran dan Pengumpulan" 11 Sep - 11 Okt 2026.
    | Di luar rentang ini, pendaftaran tim baru dan pengumpulan karya ditolak.
    |
    | Nilainya bisa diubah lewat .env tanpa perlu deploy ulang, misalnya kalau
    | jadwal diperpanjang:
    |
    |   STECH_REG_OPENS_AT="2026-09-11 00:00:00"
    |   STECH_REG_CLOSES_AT="2026-10-11 23:59:59"
    |
    | Kosongkan salah satunya untuk menonaktifkan batas tersebut.
    |
    */

    'registration' => [
        'opens_at' => env('STECH_REG_OPENS_AT', '2026-09-11 00:00:00'),
        'closes_at' => env('STECH_REG_CLOSES_AT', '2026-10-11 23:59:59'),
    ],

    /*
    | Batas pengumpulan karya. Secara default disamakan dengan penutupan
    | pendaftaran karena timeline menyebut keduanya dalam satu periode.
    */

    'submission' => [
        'closes_at' => env('STECH_SUBMISSION_CLOSES_AT', '2026-10-11 23:59:59'),
    ],

];
