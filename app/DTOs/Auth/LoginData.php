<?php
declare(strict_types=1);

namespace App\DTOs;

use Spatie\LaravelData\Data;

class LoginData extends Data
{
    public function __construct(
        public int $email = 5,
        public int $password = 1
    ) {
    }
}