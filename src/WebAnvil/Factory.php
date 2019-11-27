<?php

namespace WebAnvil;

use \Closure;
use WebAnvil\Interfaces\ActionInterface;
use WebAnvil\Interfaces\ValidationMapInterface;
use WebAnvil\Interfaces\ValidatorInterface;
use WebAnvil\Placeholder\Response as EmptyResponse;
use WebAnvil\Placeholder\Validator as EmptyValidate;

abstract class Factory
{
    protected $factories = [];

    /** @var self */
    protected static $instance;

    protected function __construct()
    {
        // Protected construct to prevent direct instantiation
    }
    
    /**
     * @return ValidatorInterface
     * @throws \WebAnvil\ForgeClosureNotFoundException
     */
    public function validator(): ValidatorInterface
    {
        return $this->handleClosure('validate') ?? new EmptyValidate();
    }

    /**
     * @return \WebAnvil\Interfaces\ValidationMapInterface|null
     * @throws \WebAnvil\ForgeClosureNotFoundException
     */
    public function validationMap(): ?ValidationMapInterface
    {
        return $this->handleClosure('validation_map', false);
    }

    public function requestData(array $fields = null): array
    {
        try {
            $closure = $this->get('request');
        } catch (ForgeClosureNotFoundException $e) {
            return [];
        }

        return $closure($fields);
    }

    /**
     * @return \WebAnvil\Interfaces\ResponseInterface
     * @throws \WebAnvil\ForgeClosureNotFoundException
     */
    public function response()
    {
        return $this->handleClosure('response') ?? new EmptyResponse();
    }

    /**
     * @param string $message
     * @param \WebAnvil\Interfaces\ActionInterface $action
     * @return mixed
     * @throws \WebAnvil\ForgeClosureNotFoundException
     */
    public function success(string $message, ActionInterface $action)
    {
        try {
            $success = $this->get('success');

            return $success($message, $action);
        } catch (ForgeClosureNotFoundException $ignore) {}

        $response = $this->get('response');

        return $response();
    }

    /**
     * @param string $message
     * @param \WebAnvil\Interfaces\ActionInterface $action
     * @return mixed
     * @throws \WebAnvil\ForgeClosureNotFoundException
     */
    public function error(string $message, ActionInterface $action)
    {
        try {
            $error = $this->get('error');

            return $error($message, $action);
        } catch (ForgeClosureNotFoundException $ignore) {}

        $response = $this->get('response');

        return $response();
    }

    public function logThrowable(\Throwable $e): void
    {
        try {
            $logger = $this->get('log');

            $logger($e);
        } catch (ForgeClosureNotFoundException $ignore) {}
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

    public function set(string $key, Closure $closure): self
    {
        $this->factories[$key] = $closure;
        
        return $this;
    }

    public function setValidatorClosure(\Closure $validate): self
    {
        $this->set('validate', $validate);

        return $this;
    }

    public function setValidationMapClosure(\Closure $map): self
    {
        $this->set('validation_map', $map);

        return $this;
    }

    public function setLoggerClosure(\Closure $closure): self
    {
        $this->set('log', $closure);

        return $this;
    }

    public function setRequestDataClosure(\Closure $request): self
    {
        $this->set('request', $request);

        return $this;
    }

    public function setResponseClosure(\Closure $response): self
    {
        $this->set('response', $response);

        return $this;
    }

    public function setSuccessClosure(\Closure $response): self
    {
        $this->set('success', $response);

        return $this;
    }

    public function setErrorClosure(\Closure $response): self
    {
        $this->set('error', $response);

        return $this;
    }
}
