# Controller & Methods
Controllers are the central building blocks of your application’s logic.  
Each controller handles incoming requests, processes data, and determines which views or responses should be returned to the client.  
Controllers must extend the **Controller** class to access all necessary methods and properties.

## Passing Data to Templates

Controllers can pass data to view files by setting values on the `Response` object:

```php
<?php
// Example URL: yourdomain.tld/animals/tiger/indo-chinese
class AnimalController extends Controller {
	public function index(): void {
		$this->response->data["pageTitle"] = "Welcome to Umbrella Corp!";
		$this->response->setTitle("Welcome!");
		$this->response->setView("welcome-page");
	}
}
```

In the above example, the string `Welcome to Umbrella Corp!` will be available as `$pageTitle` in the view:

```php
<!-- welcome-page.tpl.php -->
<h1><?php print $pageTitle; ?></h1>
```

A view must be set by each controller using the `setView` method:

```php
class AnimalController extends Controller {
	public function index(): void {
		$this->response->setView("animal-index");
	}
}
```

The `setTitle` method is a shortcut for setting a `title` key in the response data array.  
By default, `$title` is used by the provided header partial in the `<title>` tag.

## Child Controllers

Controllers can specify child controllers to be executed after the parent controller:

```php
<?php
class AnimalController extends Controller {
	public function index(): void {
		$this->children[] = "TigerController";
	}
}
```

In this example, **TigerController** will be invoked after **AnimalController**.  
Only the `index` method is called on child controllers.  
Response data set by a parent controller is accessible and modifiable by its children.

You may throw a `\Core\HttpError\NotFound` to reroute the stack to **NotFoundController**,  
or throw a `\Core\HttpError\Forbidden` to reroute to **ForbiddenController**.

## Namespaced Controllers

Controllers can be namespaced for partials and complex structures.  
For example, you can use namespaces like `Partial` or `StatusCode`:

```php
<?php
class PageController extends Controller {
	public function index(): void {
		// Add partials as child controllers
		$this->children[] = new ClassName("Partial\Alerts");
		$this->children[] = new ClassName("Partial\Sidenav");
	}
}
```

Example namespaced controllers:

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

This assumes you have view files like `alerts.tpl.php` in a `partials` folder.  
See [Template and theming](./Template_and_theming.md) for more details.

## Inherited Controller Properties

Controllers extending the base `Controller` class inherit several pre-wired properties for routing, templating, content negotiation, and more.  
These are set automatically and should not be manually overwritten.

### `$parent`
- **Type:** `null|\Controller`
- **Description:** Reference to the parent controller, if any.
- **Usage Example:**
  ```php
  if ($this->parent instanceof \AuthController) { /* ... */ }
  if ($this->parent === null) { /* top-level controller */ }
  ```

### `$request`
- **Type:** `\Core\Request`
- **Description:** Represents the current HTTP request (GET, POST, files, cookies, etc.).
- **Usage Example:**
  ```php
  $this->request->get;
  $this->request->post;
  $this->request->files;
  $this->request->cookies;
  $this->request->server;
  ```

### `$response`
- **Type:** `\Core\Response`
- **Description:** Handles output data and headers.
- **Usage Example:**
  ```php
  $this->response->data['title'] = 'My Page';
  $this->response->setStatusCode(404);
  ```

### `$application`
- **Type:** `\Core\Application`
- **Description:** The root application instance.
- **Usage Example:**
  ```php
  $this->application->getExecutedClassName();
  $this->application->getCalledMethodName();
  ```

### `$router`
- **Type:** `\Core\Router`
- **Description:** Manages routing state and request/response resolution.
- **Usage Example:**
  ```php
  $path = $this->router->getRoute();
  ```

### `$template`
- **Type:** `\Core\Template`
- **Description:** Handles asset injection and layout template resolution.
- **Usage Example:**
  ```php
  $view = $this->template->getViewPath("partial/sidenav");
  $path = $this->template->getDirectoryUri("assets/img/logo.png");
  ```

### `$assets`
- **Type:** `\Core\Assets`
- **Description:** Manages asset registration and injection (CSS, JS, images).
- **Usage Example:**
  ```php
  $this->assets->addStylesheet("assets/css/styles.css");
  $this->assets->addJavascript("assets/js/app.js");
  ```

### `$contentType`
- **Type:** `\Core\ContentType\ContentTypeInterface`
- **Description:** Holds the negotiated content type (HTML, JSON, XML, etc.).
- **Usage Example:**
  ```php
  if ($this->contentType::class === \Core\ContentType\Html::class) {
	  // Render HTML
  }
  ```

### `$renderer`
- **Type:** `\Core\Renderer`
- **Description:** Combines response data, template, and content type into output.
- **Usage Example:**
  ```php
  $this->renderer->render($this->response);
  ```

### `$children`
- **Type:** `array`
- **Description:** List of child controllers to execute after the main one (used for layouts/partials).
- **Usage Example:**
  ```php
  $this->children[] = new ClassName('Partial\\Sidebar');
  ```

## Accessors / Getters

While the above properties are `protected` and can be access by inheritance, they each have a corresponding getX() method:
- `getRequest()` → `$request`
- `getResponse()` → `$response`
- `getApplication()` → `$application`
- `getRouter()` → `$router`
- `getTemplate()` → `$template`
- `getChildren()` → `$children`
- `getParent()` → `$parent`