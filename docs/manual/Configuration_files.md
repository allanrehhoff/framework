# Configuration files
A superset of json, called jsonc is used for configuration files, this is to support comments in configuration files.  

All config files are to be located in the `storage/config` directory.  
The main configuration resides within the file `application,jsonc`, and should contain nothing but configuration settings used by the core and controllers.  

Theme specific configurations such as assets, third-party libraries should be managed by the `default.theme.jsonc` or their respective `.jsonc` file file bundled with the theme.  

Values can be accessed, changed, removed and saved using a dot syntax.  

```php
<?php
	class RestaurantController extends \Controller {
		public function __construct() {
			\Singleton::getConfigiration()->get("menu.pizzas"); // ["Hawaii", "MeatLover", "Vegan", ...]
			\Singleton::getConfigiration()->set("menu.pizzas.Hawaii", "Ananas"); // ["Ananas", "MeatLover", "Vegan", ...]
			\Singleton::getConfigiration()->remove("menu.pizzass.Vegan"); // ["Ananas", "MeatLover", ...]
		}
	}
```

Configuration values may contain variables, structured as {{key}}, where the key between the {{ }} brackets should map to another key in the same configation file.
The value from such key, will then be replaced upon retrieval with `->get()`

Examples:
```json
"version": "1.0.0",
"site_name": "Framework",
"http": {
    "useragent": "{{site_name}}/{{version}}"
}
```

```php
// The following would return Framework/1.0.0
\Singleton::getConfiguration()->get("http.useragent");
```

Variables are parsed recursively, and therefore values from nested objects can also be used, using a dot syntax.
```json
"version": {
	"dev": "1.0.0",
	"prod": "1.0.0"
},
"site_name": "Framework",
"http": {
    "useragent": "{{site_name}}/{{version.prod}}"
}
```

A limited set of standard PHP functions are supported to return values from config files.
There are `getenv`, `constant`, `defined` and `ini_get`

Using getenv();  
```json
"version": "constant('PHP_VERSION')",
"site_name": "Framework",
"database": {
    "DB_HOST": "getenv('DB_HOST')",
	"DB_USER": "getenv('DB_USER')",
	"DB_PASS": "getenv('DB_PASS')",
	"DB_NAME": "getenv('DB_NAME')"
}
```

The core base_title setting only supports one wildcard %s use **(controller)->setTitle($title)** in your controller files to set a dynamic title.  

Custom wildcards and variables aren't affected in other configuration values.  
