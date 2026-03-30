<?php

declare(strict_types=1);

namespace Janderson\TinyStack;

/**
 * A tiny middleware stack dispatcher
 *
 * Rules:
 * - Middlewares must implement this interface: `function(callable $next, array &$envelope, mixed ...$arguments): mixed`
 * - Middlewares can mutate arguments by passing arguments to the next function. Omitting them leaves arguments unchanged.
 * - Each middleware is responsible for calling the next function and returning its result or a value.
 * - If there is no terminal middleware, next will return its singular argument, or its arguments when an array
 *
 * The most basic usage is simply to execute the stack with some middlewares:
 * ```php
 * $trimMiddleware = fn($next, &$envelope, $input) => $next(trim($input));
 * $logMiddleware = function($next, &$envelope, ...$args) { error_log("log: " . json_encode($args)); return $next(...$args); };
 * $upperMiddleware = fn($next, $env, $input) => $next(strtoupper($input));
 * $stack = stack($trimMiddleware, $logMiddleware, $upperMiddleware);
 * echo $stack(" Hello, world! "); // outputs "HELLO, WORLD!"
 * ```
 */
function stack(callable ...$middlewares): callable {
    $envelope = [];
    $next = function (callable $next, mixed ...$args) use(&$middlewares, &$envelope): mixed {
        $middleware = current($middlewares);
        next($middlewares);
        $localNext = fn(mixed ...$localArgs): mixed => $next($next, ...($localArgs ?: $args));
        return $middleware ? $middleware($localNext, $envelope, ...$args) : (count($args) === 1 ? $args[0] : $args);
    };
    $envelope = [];
    return fn(...$arguments) => $next($next, ...$arguments);
}