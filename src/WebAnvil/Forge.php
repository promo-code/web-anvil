<?php

namespace WebAnvil;

abstract class Forge
{
    private static $forges = [];

    /**
     * @param string $key
     * @param bool $throw_exception
     * @return mixed|null
     * @throws ForgeClosureNotFoundException
     */
    protected static function handleClosure(
        string $key,
        bool $throw_exception = true
    )
    {
        $closure = self::get($key, $throw_exception);

        if (null === $closure) {
            return null;
        }

        return $closure();
    }

    /**
     * @param string $key
     * @param bool $throw_exception
     * @return \Closure|null
     * @throws ForgeClosureNotFoundException
     */
    public static function get(
        string $key,
        bool $throw_exception = true
    ): ?\Closure
    {
        if ($throw_exception && empty(self::$forges[$key])) {
            throw new ForgeClosureNotFoundException(
                "Closure for {$key} not found"
            );
        }

        return self::$forges[$key] ?? null;
    }

    public static function set(string $key, \Closure $closure): void
    {
        self::$forges[$key] = $closure;
    }
}
