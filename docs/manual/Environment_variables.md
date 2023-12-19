# Environment variables
To use a dotenv file create `.env` in the `src/` directory.
It'll be parsed automatically if present.  

The `\Environment` class is used to integrate with environment variables.

To read a variable
```php
<?php
\Registry::getEnvironment()->get("MAINTENANCE_MODE")
```

if the name provided was defined as a section, the whole section may be returned as an array.

```php
<?php
// Example return value:
// ["HOST" => "...", "USER" => "...", "PASS" => "...", "NAME" => "..."]
\Registry::getEnvironment()->get("DATABASE")
```

Variables in sections may be also accessed using a dot notation.

```php
<?php
\Registry::getEnvironment()->get("DATABASE.HOST");
\Registry::getEnvironment()->get("DATABASE.USER");
\Registry::getEnvironment()->get("DATABASE.PASS");
\Registry::getEnvironment()->get("DATABASE.NAME");
```

To set enrionment variables dynamically.

```php
<?php
// Single value
\Registry::getEnvironment()->put("VARNAME", "value");

// To a section
\Registry::getEnvironment()->put("SECTION", ["ONE" => "hello world"]);
// ... or
\Registry::getEnvironment()->put("SECTION.ONE", "hello world");
```

Variable names will be automatically converted to uppercase.

```php
<?php
\Registry::getEnvironment()->put("varname", "value");
// The above will likewise be accessible with
\Registry::getEnvironment()->get("VARNAME", "value");
```