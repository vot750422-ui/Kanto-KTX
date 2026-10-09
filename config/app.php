<?php

// WAMP uses a subdirectory; Docker sets APP_BASE_PATH=/.
function appUrl(string $path = ''): string
{
    $base = getenv('APP_BASE_PATH');
    $base = $base === false ? '/Kanto-KTX' : $base;
    return '/' . trim(trim($base, '/') . '/' . ltrim($path, '/'), '/');
}
