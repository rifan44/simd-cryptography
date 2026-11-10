<?php

declare(strict_types=1);

namespace App\Middleware;

final class AuthMiddleware
{
    public static function enforce(): void
    {
        if (empty($_SESSION['user']['id'])) {
            \redirect('login');
        }
    }
}
