# Coding Standards

## General
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

## Language
English must be used in all circumstances. This includes:
- All variable, function, method, and class names
- All comments and docblocks
- All error messages and logging output
- All documentation and git commit messages

Only exception is for user-facing text, if the general application language is non-english.  

✅ Good
```php
$userName = "John";
$isValidated = true;

/**
 * Retrieve the user's email address.
 */
public function getUserEmail(): string {
    return $this->email;
}
```

⛔ Bad
```php
$nombreDelUsuario = "John"; // Spanish name
$estáValidado = true; // Spanish name

/**
 * Obtener la dirección de correo electrónico del usuario.
 */
public function getCorreoDelUsuario(): string {
    return $this->email;
}
```

## Indentation and Braces
- Use tabs for indentation.
- Opening brace for classes, functions, and control structures goes on the same line as the statement.

## Keywords and Syntax
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

## Arrays and built-in alternatives
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

## Type hints
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

## Naming Conventions

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

### Casing
Variables, functions and methods must use camelCase and never snake_case:
Namespaces always PascalCase.  

✅ Good
```php
namespace User;

class User {
    private string $userName;
    
    public function getUserName(): string {
        return $this->userName;
    }
    
    public function validateUserInput(): bool {
        return true;
    }
}
```

⛔ Bad
```php
namespace user;

class User {
    private string $user_name; // snake_case
    
    public function get_user_name(): string { // snake_case
        return $this->user_name;
    }
    
    public function validate_user_input(): bool { // snake_case
        return true;
    }
}
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

## Namespaces
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

## Docblocks
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

## Commenting and TODO
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



## Coding standard configuration notes
- Includes `SlevomatCodingStandard.TypeHints.LongTypeHints`.
- Enforces no unused function parameters (`Generic.CodeAnalysis.UnusedFunctionParameter`).
- Enforces upper-case constants (`Generic.NamingConventions.UpperCaseConstantName`).
- Disallows space indentation (`Generic.WhiteSpace.DisallowSpaceIndent`).
- Disallows disallowed short open tags (`Generic.PHP.DisallowShortOpenTag`).

---

Keep standards in sync with `phpcs.xml` to preserve consistent enforcement.
