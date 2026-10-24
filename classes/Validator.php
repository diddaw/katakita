<?php

class Validator
{
    public static function required($value): bool
    {
        return trim((string) $value) !== '';
    }

    public static function email($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function minLength($value, int $length): bool
    {
        return strlen((string) $value) >= $length;
    }

    public static function inArray($value, array $allowedValues): bool
    {
        return in_array($value, $allowedValues, true);
    }
}