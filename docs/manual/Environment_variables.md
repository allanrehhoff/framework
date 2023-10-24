# Environment variables
To use a dotenv file create `.env` in the `src/` directory.
It'll be parsed automatically if present.  

The `\Environment` class is used to integrate with environment variables.

To read a variable
```php
<?php
\Singleton::getEnvironment()->get("MAINTENANCE_MODE")
```

if the name provided was defined as a section, the whole section may be returned as an array.

```php
<?php
// Example return value:
// ["HOST" => "...", "USER" => "...", "PASS" => "...", "NAME" => "..."]
\Singleton::getEnvironment()->get("DATABASE")
```

Variables in sections may be also accessed using a dot notation.

```php
<?php
\Singleton::getEnvironment()->get("DATABASE.HOST");
\Singleton::getEnvironment()->get("DATABASE.USER");
\Singleton::getEnvironment()->get("DATABASE.PASS");
\Singleton::getEnvironment()->get("DATABASE.NAME");
```

To set enrionment variables dynamically.

```php
<?php
// Single value
\Singleton::getEnvironment()->put("VARNAME", "value");

// To a section
\Singleton::getEnvironment()->put("SECTION", ["ONE" => "hello world"]);
// ... or
\Singleton::getEnvironment()->put("SECTION.ONE", "hello world");
```

Variable names will be automatically converted to uppercase.

```php
<?php
\Singleton::getEnvironment()->put("varname", "value");
// The above will likewise be accessible with
\Singleton::getEnvironment()->get("VARNAME", "value");
```