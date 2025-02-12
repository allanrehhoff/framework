# The Str utility class

The `\Str` class can be used for null-aware string manipukation.  
Handles null values gracefully, ensuring that methods return a value of a similar or predictable type.
## safe
```php
// Escapes provided string for use in client-side HTML
\Str::safe("Hello, World!");
```

## encode
```php
// Returns "SGVsbG8sIFdvcmxkIQ==", empty string if null
\Str::encode("Hello, World!");
```

## decode
```php
// Returns "Hello, World!", empty string if null
\Str::decode("SGVsbG8sIFdvcmxkIQ==");
```

## len
```php
// Returns 13, 0 if null
\Str::len("Hello, World!");
```

## lower
```php
// Returns "hello, world!", empty string if null
\Str::lower("Hello, World!");
```

## upper
```php
// Returns "HELLO, WORLD!", empty string if null
\Str::upper("Hello, World!");
```

## contains
```php
// Returns true, false if null
\Str::contains("Hello, World!", "World");
```

## containsIgnoreCase
```php
// Returns true, false if null
\Str::containsIgnoreCase("Hello, World!", "world");
```

## startsWith
```php
// Returns true, false if null
\Str::startsWith("Hello, World!", "Hello");
```

## startsWithIgnoreCase
```php
// Returns true, false if null
\Str::startsWithIgnoreCase("Hello, World!", "hello");
```

## endsWith
```php
// Returns true, false if null
\Str::endsWith("Hello, World!", "World!");
```

## endsWithIgnoreCase
```php
// Returns true, false if null
\Str::endsWithIgnoreCase("Hello, World!", "world!");
```

## test
```php
// Returns true, false if null
\Str::test("/\d+/", "123");
```

## match
```php
// Returns ["123"], empty array if null
\Str::match("/\d+/", "123");
```

## matchAll
```php
// Returns ["Hello", "World"], empty array if null
\Str::matchAll("/\w+/", "Hello, World!");
```

## ascii
```php
// Returns 'cliche', empty string if null
\Str::ascii("cliché");
```
