<?php

namespace App\Support;

abstract class AbstractEnum
{
    public static function all() : array
    {
        $reflection = new \ReflectionClass(static::class);
        return array_values($reflection->getConstants());
    }

    public static function stringify() : string
    {
        return implode(",", self::all());
    }

    public static function toArray() : array
    {
        return array_values(
            array_map(function ($value) {
                return ['value' => $value, 'label' => ucfirst($value)];
            }, self::all())
        );
    }
}
