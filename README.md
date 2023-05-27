# Custom PHP Framework - Introduction (Version 7)
A lightweight custom framework using well-known and intuitive patterns, without the need for manually defining routes.

This framework will not be bundled with bloatware such as modules/components/addons/plugins or other third-party libraries.   
The only exception to this rule is jQuery, while it is not strictly required, or mandatory to use, it simply is less verbose than native javascript, and often require fewer steps to achieve a certain task.  
It is completely safe to remove this from the default theme files if preffered.  

In short, all this does is serve as a kickstart to get a readable and stable codebase when starting up a new custom web project.

# Documentation

## Application Directory
Because configuration files should not reside in the same directory as the application root, you must configure your server to set the **application/** as it's **DocumentRoot**.  

## Configuration
The directory **storage/config/application.jsonc** file holds all application-wide configurations, such as database credentials, and other settings required by your application.  

At least one theme configuration file should be present, **default.theme.jsonc** is bundled with this package.  

In general the **storage/** directory should be configured to store all non-code files.  

## Controller & Methods
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
The **\Resource::getRouter()->arg();** method starts from index 0, whereas the first two indices are already used by the core to determine the route.  

```php
<?php
	// Assume this url: yourdomain.tld/animals/tiger/indo-chinese
	class AnimalController extends Controller {
		public function index() {
			// Will only trigger at yourdomain.tld/animal
		}

		public function tiger() {
			var_dump(\Resource::getRouter()->arg(2)); // indo-chinese
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
		public function index() {
			$this->children[] = "TigerController";
		}
	}
```
Will result in **TigerController** being invoked as if it was a normal controller, AFTER **AnimalController**

A view must be by each controller, throught the **Controller::setView();**

Children controllers will be able to set or modify any data set by the parent controller.  

In any controllers of the heirachy you may throw a `\Core\Exceptions\NotFound` to reroute the entire stack to **NotFoundController**   
You may also throw a `\Core\Exceptions\Forbidden` to instead reroute to **ForbiddenController**

> *NOTE:*  
> The default method invoked is **index** this will happen if arg(1) is nowhere to be found in the given controller, or arg(1) is void.

> *NOTE:*  
> **index** will also be the method called for all children controllers.

## Themes folder
This is where all your theming goes (obviously du'h).  
Each theme should contain at least the following files.  

- header.tpl.php (Required)  
- footer.tpl.php (Required)  
- (default-route).tpl.php (Required) (default-route indicates a filename matching the configured default route.)  
- notfound.tpl.php (Required)  
- THEMENAME.theme.jsonc (Required) (This is the per-theme configurations, this file should be located in the config/ directory)

Theme configuration files must be located in the **storage/config directory**  

It is assumed by the core that your theme has at least the required files, failing to create those files will result in unknown errors.  
  
Every view file must have the extension **.tpl.php** this is to distinguish them from their representative controller files.  
By default the view to be displayed is the one found matching arg(0), for example **animal.tpl.php**, unless otherwise specified by the dispatched controller.

You can add a new "partial" or "children" by adding it's path to the controllers data.
```php
<?php
$this->response->data["sidebar"] = $this->theme->getTemplatePath("header");
```

And then in your template files

```php
<?php
require $sidebar;
```

Theme assets should be configured in the **THEMENAME.theme.jsonc** file, and paths must reside in the **storage/config/** directory.  

> *NOTE:*  
> header.tpl.php, footer.data.php, and any other view files you plan to include or require in another view file can have a controller attached, if they were invoked as a child controller, see Controllers and Methods section.

## Command Line Interface
This framework supports being queried through CLI (albeit, not fully tested), to do so you must query the **index.php** file.  
Or use the bundled **bin/app** file, remember to add execution permissions to this file, should you decide to use it.  

```
$ php index.php <controller> <method> <argument> ...
```
  
Just as the URL scheme, the first argument maps to the controller being used, second the method and so on.  
```
$ php index.php cli
Hello from cli

$ php index.php cli interface
Hello from interface
```
  
## Configuration
A superset of json, called jsonc is used for configuration files, this is to support comments in configuration files.  

> *NOTE:*  
> ConfigurationParser still only has partial support for jsonc.  

All config files are to be located in the **storage/config** directory.  
The main configuration resides within the file **application,jsonc**, and should contain nothing but configuration settings used by the core and controllers.  

Theme specific configurations such as assets, third-party libraries should be managed by the **default.theme.jsonc** file bundled with the theme.  

Configuration is loaded upon controller initialization.  
Values can be accessed, changed, removed and saved using a dot syntax.  
```php
<?php
	class RestaurantController extends \Controller {
		public function __construct() {
			$this->theme->get("menu.pizzas"); // ["Hawaii", "MeatLover", "Vegan", ...]
			$this->theme->set("menu.pizzas.Hawaii", "Ananas"); // ["Ananas", "MeatLover", "Vegan", ...]
			$this->theme->remove("menu.pizzass.Vegan"); // ["Ananas", "MeatLover", ...]
			$this->theme->save();
		}
	}
```

Configuration values may contain variables, structured as {{key}}, where the key between the {{ }} brackets should map to another key in the same configation file.
The value from such key, will then be replaced upon retrieval with **->get()**

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
\Resource::getConfiguration()->get("http.useragent");
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

The core base_title setting only supports one wildcard %s use **(controller)->setTitle($title)** in your controller files to set a dynamic title.  

Custom wildcards and variables aren't affected in other configuration values.  

## Autoloading classes
Autoloading is a mechanism which requires class, interface and trait definitions (from here on, referenced as instances) on demand.  
Files containing the definition of a class must share name with the class name, and end on **.php** Obviously...
Additionally instances residing within a namespace must be located within a folder structure matching the the namespacing structure (relative from classes/ folder).  
  
## Errors and Exceptions
The application comes bundled with a rather conservative error/exception handlers, the handlers are very aggresive and will take care of generating a small stacktrace for debugging purposes.  
Every PHP notice/error is treated as a fatal error by the error handler, this is to prevent the next developer from banging his head into the table later on, as those errors should be dealt with during development.  
  
However if you do decide to be a nincompoop and annoy the next developer you can turn of error reporting entirely by using the **ini_* ** functions in **preprocess.php**

The exception handler will still kill your application however, due to exceptions being thrown around.  

Good practice dictates that while developing your custom classes you should also create custom exceptions in the same namespace to match your classes.  
  
## The Resource Class
Is where all instances that should be globally accessible is stored.  

Once an instance has been set in the Resource, it is immediately accesible by using **Resource::get()** instances are keyed by their class name definitions.  
The instance registered, will be returned.  
  
Examples:  
```php
<?php
	// Exmaple 1
	$currentUser = \Resource::set(new User($uid));

	// Example 2
	$currentUser = \Resource::get("User");
```

In case an instance is namespaced the namespace should also be specified (without the initial backslash) upon retrieval.

Example:  
```php
<?php
	\Resource::set( new \Alerts\Error("No more cheese for the pizza...") );

	print \Resource::get("Alerts\Error")->getMessage(); // Would print: "No more cheese for the pizza"
```
  
This structure is in place to avoid singletons being misused.  
  
## Database Entities  
For easier data manipulation, data objects should extend the **\Database\Entity** class.  
Every class that extends **\Database\Entity** must implement the following methods.  

- getTableName(); // Table in which this data object should store data.  
- getKeyField(); // The primary key of the table in which this object stores data.  

Inspect the bundled \Database\EntityType.php file, for en example on how to write an entity class.  
> *NOTE:*  
> Entities does not have to reside in the Database namespace.

Every data object take an optional parameter [(int) primary_key] upon instantiating,  
identifying whether a new data object should be instantiated or an already existing row should be loaded from the table.  

If you wish to change data use the **->set(['column' => 'value']);**  
This will allow you to call **->save();** on an object and thus saving the data to your database.  
The data object will be saved as a new row if the primary_key key parameter was not present upon instantiating.  

An entity may also be constructed by passing an array to the constructor.  
If a key matching the column of the primary key is found, the row will be loaded from the database.  

## Database

Use the \Database\Connection class to perform manual queries if needed.  

```php
<?php
\Resource::getDatabaseConnection()->query("UPDATE animals SET `extinct` = :value WHERE name = :name", ["value" => true, "name" => "Asian Rhino"]);
```

This could also be written as follows:  
```php
<?php
\Resource::getDatabaseConnection()->update("animals", ["extinct" => true], ["name" => "Asian Rhino"]);
```

Queries with a return value will be fetched as objects, for instance:  
```php
<?php
\Resource::getDatabaseConnection()->select("animals");
```

```php
<?php
\Resource::getDatabaseConnection()->update("animals", ["extinct" => true], ["name" => "Asian Rhino"]);
```

```php
<?php
\Resource::getDatabaseConnection()->insert("animals", ["name" => "Sumatran Tiger", "extinct" => false]);
```

Advanced filters are also suported in where clauses.

```php
<?php
// Perform when WHERE .. IN (...)
\Resource::getDatabaseConnection()->select("animals", ["name" => ["Asian Rhino", "Sumatran Tiger"]]);
```

```php
<?php
// Uses the spaceship-operator in MySQL
\Resource::getDatabaseConnection()->select("animals", ["name" => NULL]);
```

```php
<?php
// Uses the spaceship-operator in MySQL
\Resource::getDatabaseConnection()->search("animals", ["name NOT IN :animalNames"], ["animalNames" => ["Indo chinese", "Alligator"]]);
```

## The Assets class
In the DOM namespace you'll find the Document class, this can be used to add stylesheets and javscript to the page.  
Do either of the following to achieve this.  
**\Resource::getAssets::addStylesheet();**, **\Resource::getAssets()->addJavascript();** methods.  
ressources are rendered in the same order they are added  
  
If you desire to add custom media stylesheets make use of the second parameter **$media** in **\\Resource::getAssets()->addStylesheet();**  
Same goes for the **\\Resource::getAssets()->addJavascript();** method for other regions than the footer.  