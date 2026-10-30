<?php

namespace App\Demo\Auth;

/*
| Pengguna demo sederhana, bukan model database dan bukan autentikasi asli.
| Cukup punya properti ->role seperti contoh materi CheckUserRole.
*/
final class DemoUser
{
    public function __construct(
        public string $role,
        public string $name,
    ) {}
}
