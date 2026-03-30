<?php

declare(strict_types=1);

namespace Janderson\TinyStack;

/**
 * A tiny middleware stack dispatcher
 *
 * Middlewares are expected to have the following function signature:
 * ```php
 * function (callable $next, array &$envelope, mixed ...$arguments): mixed
 * ```
 */
function stack(callable ...$middlewares): callable {
    $envelope = [];
    $next = function (callable $next, mixed ...$args) use(&$middlewares, &$envelope): mixed {
        $middleware = current($middlewares);
        if (!$middleware) {
            return count($args) === 1 ? $args[0] : $args;
        }
        next($middlewares);
        $localNext = fn(mixed ...$localArgs): mixed => $next($next, ...($localArgs ?: $args));
        return $middleware($localNext, $envelope, ...$args);
    };
    $envelope = [];
    return fn(...$arguments) => $next($next, ...$arguments);
}