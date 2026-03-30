# tiny-stack

A tiny middleware stack dispatcher for PHP.

## Installation

```bash
composer require janderson/tiny-stack
```

## Usage

Middlewares must implement a function signature compatible with:
```php
function(callable $next, array &$envelope, mixed ...$args): mixed
```

A minimal example:
```php
use function Janderson\TinyStack\stack;

$trim  = fn($next, &$envelope, $input) => $next(trim($input));
$upper = fn($next, &$envelope, $input) => $next(strtoupper($input));

$stack = stack($trim, $upper);
echo $stack(' Hello, world! '); // "HELLO, WORLD!"
```

### Middleware Rules

- Each middleware calls `$next` and returns its result (or its own value).
- Pass arguments to `$next` to mutate them; omit them to preserve the originals.
- The `$envelope` array is shared across all middlewares for passing state.
- Without a terminal middleware, the stack returns a single argument as-is or multiple arguments as an array.

## License

MIT
