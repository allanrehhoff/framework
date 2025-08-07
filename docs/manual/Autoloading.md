# Autoloading classes
Autoloading Behavior

The framework uses a custom autoloading scheme.  
In this scheme, class files must match their class names and `.php` extension, and namespaces must correspond to directory structure under the `src/Libraries/` or `src/Controllers` folder.  

Specifically:  
- **Controllers:** Any controller class is placed in `src/Controllers/` and is routed automatically. fx `src/Controllers/AuthController.php` will be routed from URI `/auth`
- **Other Classes (Libraries):** All other classes (services, models, utilities, etc.) are placed under `src/Libraries/`  
	The namespace (if used) must mirror subfolders of src/Libraries. For example, `src/Libraries/User.php` defines a User class, and `src/Libraries/Store/Product.php` defines class `Store\Product`, matching the `Store/` subdirectory.
- **Composer Autoloader:** If the project includes a `vendor/` directory, either at the top-level or inside `src/`, the framework will register Composer’s autoloader automatically.

```php
<?php
// File: src/Controllers/AuthController.php
class AuthController {
	public function index(): void {
		// ...
	}
}
```

Other classes are autoloaded from the `src/Libraries` directory.  

```php
<?php
// File: src/Libraries/User.php
class User {
	public function authorize(): bool {
		// ...
	}
}
```

The `composer` generated autoloader will be registered, when the `vendor` directory is present in either document root, or its parent.