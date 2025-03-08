<?php

class Environments
{
    public static function read($path = null): array
{
    if (!$path) {
        $path = __DIR__ . '/../../../.env';
    }

    $result = [];
    $lines = file($path);

    foreach ($lines as $line) {

        if (trim($line) === '' || str_starts_with(trim($line), '#')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        $result[$key] = $value;
    }

    
    return $result;
}

}
