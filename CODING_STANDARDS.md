# Coding Standards

## 1. General
- No PHP short open tags (`<?` and `<?=`). Use `<?php` only.
- Files under `src/` are in scope for style enforcement.
- Variables must never be used inside double quoted strings.

✅ Good
```php
$name = "World";
$message = 'Hello ' . $name . '!';
$message = sprintf('Hello %s!', $name);
```

⛔ Bad
```php
$name = "World";
$message = "Hello $name!"; // Variable interpolation
$message = "Hello {$name}!"; // Curly brace syntax
```

## 2. Indentation and Braces
- Use tabs for indentation.
- Opening brace for classes, functions, and control structures goes on the same line as the statement.

## 3. Keywords and Syntax
- Add a single space after control keywords: `if`, `foreach`, `while`, `switch`, etc.
- Boolean, control and PHP keywords are lower-case.

✅ Good
```php
if ($value === null) {
	return false;
}
```

⛔ Bad
```php
if($value===null){
	return false;
}
```

## 4. Arrays and built-in alternatives
- Use square-bracket syntax for arrays: `[]`.
- Avoid old array syntax: `array()`.
- Avoid forbidden direct functions:
  - use `print` instead of `echo`
  - do not use `create_function`; use closures or named functions.

✅ Good
```php
$items = [];
print "Hello world";
$callback = function() { return true; };
```

⛔ Bad
```php
$items = array();
echo "Hello world";
$callback = create_function('', 'return true;');
```

## 5. Type hints
- Prefer explicit type declarations in signatures.
- Prefer union types for nullable values: `null|string` rather than `?string` for consistency with configured long type hints.

✅ Good
```php
function isPalindrome(null|string $string): bool {
	return $string !== '' && $string === strrev($string);
}
```

⛔ Bad
```php
function isPalindrome(?string $string): bool {
	return $string !== '' && $string === strrev($string);
}
```

## 5.1 Naming Conventions

### Variables
Variables should be named after the object they hold, prefixed with a lowercase 'i' for instances:

✅ Good
```php
$iUser = new \User();
$iConfiguration = \Registry::getConfiguration();
```

⛔ Bad
```php
$user = new \User();
$config = \Registry::getConfiguration();
```

### Classes and Entities
Entity classes and database table names should always be in singular form, never plural:

✅ Good
```php
class User extends \Database\Entity {
    public static function getTableName(): string {
        return 'user';
    }
}

class UserSession extends \Database\Entity {
    public static function getTableName(): string {
        return 'userSession';
    }
}
```

⛔ Bad
```php
class Users extends \Database\Entity { // Plural
    public static function getTableName(): string {
        return 'users'; // Plural
    }
}
```

## 5.2 Namespaces
When using globally namespaced classes, always start with `\` inside controllers or classes.  
The use of the `use` keyword is still permitted for imports:

✅ Good
```php
<?php
use Database\Connection;

class MyController extends Controller {
    public function index(): void {
        $iConnection = new \Database\Connection();
        // or
        $iConnection = new Connection(); // if imported via use
    }
}
```

⛔ Bad
```php
class MyController extends Controller {
    public function index(): void {
        $iConnection = new Database\Connection(); // Missing leading \
    }
}
```

## 6. Docblocks
- Docblock descriptions must be separated from `@param`, `@return`, and other tags by one blank line.
- `@param` must include type and name; type can be short scalar (`int`, `bool`) for PHP 7/8 typed signatures.
- Skipped checks: inheritdoc-based function comments, parameter case and spacing variants as configured.

✅ Good
```php
/**
 * Verify whether text is palindrome.
 *
 * @param null|string $string Input value.
 * @return bool
 */
function isPalindrome(null|string $string): bool {
	// ...
}
```

## 7. Commenting and TODO
- Remove `@todo` comments before merging; temporary todos are caught by `Generic.Commenting.Todo`.

✅ Good
```php
/**
 * Process user authentication
 */
public function authenticate(): bool {
    // Validate credentials
    return $this->validateCredentials();
}
```

⛔ Bad
```php
/**
 * Process user authentication
 * @todo Implement credential validation
 */
public function authenticate(): bool {
    // TODO: Add validation logic
    return false;
}
```

## 8. Coding standard configuration notes
- Includes `SlevomatCodingStandard.TypeHints.LongTypeHints`.
- Enforces no unused function parameters (`Generic.CodeAnalysis.UnusedFunctionParameter`).
- Enforces upper-case constants (`Generic.NamingConventions.UpperCaseConstantName`).
- Disallows space indentation (`Generic.WhiteSpace.DisallowSpaceIndent`).
- Disallows disallowed short open tags (`Generic.PHP.DisallowShortOpenTag`).

---

Keep standards in sync with `phpcs.xml` to preserve consistent enforcement.
