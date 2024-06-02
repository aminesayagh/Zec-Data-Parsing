<?php

declare(strict_types=1);

namespace Zec\Utils;


use Zec\Zec;

if (!function_exists('zec')) {
    function zec(Zec|array|null $args = null): Zec {
        return new Zec($args);
    }
}

if (!function_exists('is_zec')) {
    function is_zec($value): bool {
        return is_a($value, Zec::class);
    }
}


if (!function_exists('z')) {
    
    function z(Zec|array|null $args = null): Zec {
        return zec($args);
    }
}