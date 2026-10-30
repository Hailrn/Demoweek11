<?php

/*
| Pesan validasi Bahasa Indonesia untuk aturan yang dipakai demo.
| Aturan lain otomatis memakai fallback bahasa Inggris dari framework.
*/
return [
    'required' => 'Kolom :attribute wajib diisi.',
    'string' => 'Kolom :attribute harus berupa teks.',
    'integer' => 'Kolom :attribute harus berupa bilangan bulat.',
    'numeric' => 'Kolom :attribute harus berupa angka.',
    'in' => 'Pilihan :attribute tidak valid.',
    'max' => [
        'string' => 'Kolom :attribute tidak boleh lebih dari :max karakter.',
    ],
    'min' => [
        'numeric' => 'Kolom :attribute tidak boleh kurang dari :min.',
        'string' => 'Kolom :attribute minimal :min karakter.',
    ],
    'attributes' => [
        'name' => 'nama',
        'category' => 'kategori',
        'price' => 'harga',
        'stock' => 'stok',
        'description' => 'deskripsi',
        'role' => 'peran',
    ],
];
