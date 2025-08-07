# Routing
Request URI's are automatically routed to controllers in the format: `/<class>/<method>`.

Class will be sanitized to a PascalCase class name.  
Method will be sanitized to a compatible method name in camelCase format.

This means that dashes, numbers, everything that's not a valid letter will be stripped and the upcoming word will be uppercased.

By design there's no way for PHP to validate that you (namely the developer),
define your methods in camelCaseFormat, so please! for you and the next developers sake, do this, and be strict about it. 

fx.  
**yourdomain.tld/animal/tiger** will trigger **AnimalController()->tiger();** to be called, if it is defined.  

> [!NOTE] 
> `index` is the default method invoked, if arg(1) is nowhere to be found in the given controller, or arg(1) is void.  

Anything preceeding the method is ignored, and up the the invoked controller/method to consume.  
The **$this->request->getArg();** method starts from index 0, whereas the first two indices are already used by the core to determine the route.   

```php
<?php
	// Assume this url: yourdomain.tld/animals/tiger/indo-chinese
	class AnimalController extends Controller {
		public function index(): void {
			// Will only trigger at yourdomain.tld/animal
		}

		public function tiger(): void {
			$this->request->getArg(2); // indo-chinese
		}
	}
```

The above example will output something similar to:  

```
string(12) "indo-chinese"
```

## Default route configuration
The router supports configuration through `router.jsonc` in the `storage/config` directory.

Index 0 of `defaultArgs` will be routed to a controller, while index 1 (if present) will be routed to a method.
If index 1 is ommitted the default method name `index` will be assumed.  

Default route configuration:
```jsonc
{
	"defaultArgs": [
		"index"
	]
}
```

This configuration determines which controller and method are called when accessing the root URL (/).
For example, with the above configuration:
- `yourdomain.tld/` will route to `HomeController->index()`

## Method visibility
The `Router` only allows routing to public methods of controllers.  
**Private** and **protected** methods are never accessible via routing and cannot be invoked directly through a URL.  
Only explicitly exposed functionality is accessible to clients, while internal logic remains protected.

If you need to create helper or internal methods within a controller, mark them as `private` or `protected`.  
A `NotFoundController` will be dispatched when request uri matches a `private` or `protected` method.  

## Namespaced controllers
Namespaced controllers are not routable via URL.
They are intended to be invoked as children from other controllers, not as standalone endpoints.  
Attempting to access a partial controller directly will result in a NotFound error.  