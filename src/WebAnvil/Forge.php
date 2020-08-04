<?php

namespace WebAnvil;

use \Closure;
use WebAnvil\Interfaces\ActionInterface;
use WebAnvil\Interfaces\ValidationMapInterface;
use WebAnvil\Interfaces\ValidatorInterface;
use WebAnvil\Placeholder\Response as EmptyResponse;
use WebAnvil\Placeholder\Validator as EmptyValidate;

abstract class Forge
{
    private static $forges = [];

    /**
     * @return ValidatorInterface
     * @throws \WebAnvil\ForgeClosureNotFoundException
     */
    public static function validator(): ValidatorInterface
    {
        return self::handleClosure('validate') ?? new EmptyValidate();
    }

    /**
     * @return \WebAnvil\Interfaces\ValidationMapInterface|null
     * @throws \WebAnvil\ForgeClosureNotFoundException
     */
    public static function validationMap(): ?ValidationMapInterface
    {
        return self::handleClosure('validation_map', false);
    }

    public static function requestData(array $fields = null): array
    {
        try {
            $closure = self::get('request');
        } catch (ForgeClosureNotFoundException $e) {
            return [];
        }

        return $closure($fields);
    }

    /**
     * @return \WebAnvil\Interfaces\ResponseInterface
     * @throws \WebAnvil\ForgeClosureNotFoundException
     */
    public static function response()
    {
        return self::handleClosure('response') ?? new EmptyResponse();
    }

    /**
     * @param string $message
     * @param \WebAnvil\Interfaces\ActionInterface $action
     * @return mixed
     * @throws \WebAnvil\ForgeClosureNotFoundException
     */
    public static function success(string $message, ActionInterface $action)
    {
        try {
            $success = self::get('success');

            return $success($message, $action);
        } catch (ForgeClosureNotFoundException $ignore) {}

        $response = self::get('response');

        return $response();
    }

    public static function startTransaction()
    {
        $transaction = self::get('transaction_start');

        return $transaction();
    }

    public static function commitTransaction()
    {
        $transaction = self::get('transaction_commit');

        return $transaction();
    }

    public static function rollbackTransaction()
    {
        $transaction = self::get('transaction_rollback');

        return $transaction();
    }

    /**
     * @param string $message
     * @param \WebAnvil\Interfaces\ActionInterface $action
     * @return mixed
     * @throws \WebAnvil\ForgeClosureNotFoundException
     */
    public static function error(string $message, ActionInterface $action)
    {
        try {
            $error = self::get('error');

            return $error($message, $action);
        } catch (ForgeClosureNotFoundException $ignore) {}

        $response = self::get('response');

        return $response();
    }

    public static function logThrowable(\Throwable $e): void
    {
        try {
            $logger = self::get('log');

            $logger($e);
        } catch (ForgeClosureNotFoundException $ignore) {}
    }

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
    ): ?Closure
    {
        if ($throw_exception && empty(self::$forges[$key])) {
            throw new ForgeClosureNotFoundException(
                "Closure for {$key} not found"
            );
        }

        return self::$forges[$key] ?? null;
    }

    public static function set(string $key, Closure $closure): void
    {
        self::$forges[$key] = $closure;
    }

    public static function setValidatorClosure(\Closure $validate): void
    {
        self::set('validate', $validate);
    }

    public static function setValidationMapClosure(\Closure $map): void
    {
        self::set('validation_map', $map);
    }

    public static function setLoggerClosure(\Closure $closure): void
    {
        self::set('log', $closure);
    }

    public static function setRequestDataClosure(\Closure $request): void
    {
        self::set('request', $request);
    }

    public static function setResponseClosure(\Closure $response): void
    {
        self::set('response', $response);
    }

    public static function setSuccessClosure(\Closure $response): void
    {
        self::set('success', $response);
    }

    public static function setErrorClosure(\Closure $response): void
    {
        self::set('error', $response);
    }

    public static function setTransactionStartClosure(\Closure $start)
    {
        self::set('transaction_start', $start);
    }

    public static function setTransactionCommitClosure(\Closure $commit)
    {
        self::set('transaction_commit', $commit);
    }

    public static function setTransactionRollbackClosure(\Closure $rollback)
    {
        self::set('transaction_rollback', $rollback);
    }
}
