<?php

namespace LaravelViewAnalyzer\Support;

class PathHelper
{
    public static function normalize(string $path): string
    {
        return str_replace('\\', '/', $path);
    }

    public static function makeRelative(string $path, string $base): string
    {
        $path = static::normalize($path);
        $base = static::normalize(rtrim($base, '/'));

        if (str_starts_with($path, $base)) {
            return ltrim(substr($path, strlen($base)), '/');
        }

        return $path;
    }

    public static function makeAbsolute(string $path, string $base): string
    {
        if (static::isAbsolute($path)) {
            return static::normalize($path);
        }

        return static::normalize($base.'/'.ltrim($path, '/'));
    }

    public static function isAbsolute(string $path): bool
    {
        $path = static::normalize($path);

        return str_starts_with($path, '/') || preg_match('/^[a-zA-Z]:\\//', $path);
    }
}
