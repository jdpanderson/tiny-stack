<?php

declare(strict_types=1);

namespace Janderson\TinyStack\Tests;

use function Janderson\TinyStack\stack;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\TestCase;


#[CoversFunction('Janderson\TinyStack\stack')]
class StackTest extends TestCase
{
    /**
     * Ensure a functional basic middleware chain: process input with two middlewares, returning the output
     */
    public function testMiddlewareChainProcessesAndReturnsResult(): void
    {
        $trim = fn($next, &$envelope, $input) => $next(trim($input));
        $upper = fn($next, &$envelope, $input) => $next(strtoupper($input));

        $stack = stack($trim, $upper);

        $this->assertSame('HELLO', $stack(' hello '));
    }

    /**
     * Ensure that a stack with no terminal middleware returns:
     *  - A single element when there's only one argument
     *  - An array when there are multiple arguments
     */
    public function testDefaultArgumentReturnValues(): void
    {
        $stack = stack();
        $this->assertSame('value', $stack('value'));
        $this->assertSame(['a', 'b'], $stack('a', 'b'));
    }

    /**
     * Assert that the envelope is mutably passed through each middleware
     */
    public function testMiddlewareCanMutateEnvelope(): void
    {
        $writer = function ($next, &$envelope, ...$args) {
            $envelope['key'] = 'written';
            return $next(...$args);
        };
        $link = function($next, &$envelope, ...$args) {
            $envelope['chain'] = 'linked';
            return $next(...$args);
        };
        $reader = function ($next, &$envelope, ...$args) {
            $this->assertEquals('linked', $envelope['chain']);
            return $envelope['key'];
        };

        $stack = stack($writer, $link, $reader);

        $this->assertSame('written', $stack('input'));
    }

    /**
     * Test that omitting arguments to $next() preserves original arguments
     */
    public function testMiddlewareOmittingArgsPreservesOriginal(): void
    {
        $passthrough = fn($next, &$envelope, ...$args) => $next();
        $stack = stack($passthrough);

        $this->assertSame('kept', $stack('kept'));
    }
}
