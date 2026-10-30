<?php

return [
    'name' => 'Demo Route, Middleware & Controller',

    /*
    | State demo disimpan sebagai JSON di storage/app/demo/state.json.
    | Tidak ada database sama sekali. Reset kapan pun lewat GUI atau API.
    */
    'state_path' => env('DEMO_STATE_PATH', storage_path('app/demo/state.json')),

    /*
    | Peran demo. "guest" berarti tidak ada user (request->user() === null).
    */
    'roles' => [
        'guest' => ['label' => 'Tamu', 'name' => null],
        'kasir' => ['label' => 'Kasir', 'name' => 'Kasir Demo'],
        'owner' => ['label' => 'Owner', 'name' => 'Owner Demo'],
        'admin' => ['label' => 'Admin', 'name' => 'Admin Demo'],
    ],

    /*
    | Token Bearer demo (bukan autentikasi sungguhan).
    | Header utama yang didokumentasikan tetap X-Demo-Role.
    */
    'demo_tokens' => [
        'demo-admin' => 'admin',
        'demo-owner' => 'owner',
        'demo-kasir' => 'kasir',
    ],

    'throttle' => [
        'max_attempts' => 5,
        'decay_minutes' => 1,
    ],
];
