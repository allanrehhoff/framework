# Environment variables
To use a dotenv file create `.env` in the `src/` directory.
It'll be parsed automatically if present.  

The `\Environment` class is used to integrate with environment variables.

To read a variable
```php
<?php
\Singleton::getEnvironment()->get("DATABASE.HOST")
```

if `DATABASE` is defined as a section, the whole section may be returned as an array.

```php
<?php
\Singleton::getEnvironment()->get("DATABASE")
```

To set enrionment variables dynamically.

```php
<?php
\Singleton::getEnvironment()->put("VARNAME", "value");
```

Variable names will be automatically converted to uppercase.

```php
<?php
\Singleton::getEnvironment()->put("varname", "value");
// The above will likewise be accessible with
\Singleton::getEnvironment()->get("VARNAME", "value");
```