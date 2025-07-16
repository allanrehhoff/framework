# Controller & Methods
If you're familiar with MVC frameworks you might already know the url-to-controller concept.  
Given the URL **yourdomain.tld/animal** will map to a controller file **AnimalController.php** in the application/controllers/ directory.  

Your controllers must extend upon **Controller** to have all the neccessary methods and properties available.  

Additionally the method to be called on your controller can be set by the next argument in the request URI, alias arg(1).  

**yourdomain.tld/animal/tiger** will trigger **AnimalController()->tiger();** to be called.  

arg(1) will be sanitized to a PHP5 compatible method name in camelCase format, this means that dashes, numbers, everything that's not a valid letter will be stripped
and the upcoming word will be uppercased, except the first.  

By design there's no way for PHP to validate that you (namely the developer),
define your methods in camelCaseFormat, so please! for you and the next developers sake, do this, and be strict about it. 

Any other parts beyond arg(1) ARE NOT passed directly to the controller or any methods, these are for you to pick up using the applications arg() method.  
The **$this->request->getrg();** method starts from index 0, whereas the first two indices are already used by the core to determine the route.  

> [!NOTE] 
> `index` is the default method invoked, if arg(1) is nowhere to be found in the given controller, or arg(1) is void.  
> Same method will also be the method called for all child controllers set by any parent.  

```php
<?php
	// Assume this url: yourdomain.tld/animals/tiger/indo-chinese
	class AnimalController extends Controller {
		public function index(): void {
			// Will only trigger at yourdomain.tld/animal
		}

		public function tiger(): void {
			\Registry::getRouter()->getRequest()->arg(2); // indo-chinese
		}
	}
```

The above example will output something similar to:  

```
string(12) "indo-chinese"
```

Controllers may also set child controllers to be executed once the parent controller finalizes.  

```php
<?php
	class AnimalController extends Controller {
		public function index(): void {
			$this->children[] = "TigerController";
		}
	}
```
Will result in **TigerController** being invoked as if it was a normal controller, AFTER **AnimalController**

Only the `index` method will be invoked by core on children controllers.  
To descend the request chain further, you must handle such logic manually.  

A view must be by each controller, throught the **setView();** method.
```php
class AnimalController extends Controller {
	public function index(): void {
		$this->response->setView("animal-index");
	}
}
```

Any response data set by a controller, may be accessed or altered by children through the rest of the heirachy.  

In any controllers of the heirachy you may throw a `\Core\HttpError\NotFound` to reroute the entire stack to **NotFoundController**   
You may also throw a `\Core\HttpError\Forbidden` to instead reroute to **ForbiddenController**

## Namespaced Controllers

Controllers can be namespaced to support partials and more complex application structures.  
For example, you can have controllers under a namespace such as `Partial` or `StatusCode`.

> [!IMPORTANT]
> **Namespaced controllers are not routable via URL.**  
> Namespaced controllers are not mapped directly from URLs.  
> They are intended to be invoked as children from other controllers, not as standalone endpoints.  
> Attempting to access a partial controller directly will result in a NotFound error.  

```php
<?php
class PageController extends Controller {
    public function index(): void {
        // Add a partial as a child controller
		// response data set will be passed
		// down from parent to children
        $this->children[] = new ClassName("Partial\Alerts");
        $this->children[] = new ClassName("Partial\Sidenav");
    }
}
```

Your namespaced controllers would then be similar too:

```php
<?php

namespace Partial;

class AlertsController extends \Controller {
	public function index(): void {
		$view = $this->template->getViewPath("partials/alerts");
		$this->response->data["alerts"] = $view;
	}
}
```

```php
<?php

namespace Partial;

class SidenavController extends \Controller {
	public function index(): void {
		$view = $this->template->getViewPath("partials/sidenav");
		$this->response->data["sidenav"] = $view;
	}
}
```

The above example will assume you have a view file called `alerts.tpl.php` inside a folder named `partials`.  
For more details on working with views and customizing the output, see the [Template and theming](./Template_and_theming.md) section.