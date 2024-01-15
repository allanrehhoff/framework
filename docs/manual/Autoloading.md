# Autoloading classes
Autoloading is a mechanism which requires class, interface and trait definitions.  
Files containing the definition of a class must share name with the class name, and have the extention `.php`.  
Additionally instances residing within a namespace must be located within a folder structure matching the namespacing structure (relative from `Libraries` folder).  

Controller files are autoloaded from the `src/Controllers` directory.  

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