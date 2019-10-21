<?php

namespace WebAnvil;

use \Closure;

abstract class Factory
{
    protected $factories = [];

    /** @var self */
    protected static $instance;

    protected function __construct()
    {
    }

    public static function instance(): self
    {
        if (null === self::$instance) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * @param string $key
     * @param bool $throw_exception
     * @return mixed|null
     * @throws ForgeClosureNotFoundException
     */
    protected function handleClosure(string $key, bool $throw_exception = true)
    {
        $closure = $this->get($key, $throw_exception);

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
    public function get(string $key, bool $throw_exception = true): ?Closure
    {
        if ($throw_exception && empty($this->factories[$key])) {
            throw new ForgeClosureNotFoundException(
                "Closure for {$key} not found"
            );
        }

        return $this->factories[$key] ?? null;
    }

    public function set(string $key, Closure $closure): void
    {
        $this->factories[$key] = $closure;
    }
}
