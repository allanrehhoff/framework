# Introduction (Version 4)
This is not what you'd typically associate with a fully functional MVC framework, there's no "Models" and that is intentional, deal with it.  
The intention for this is to prevent the developer from writing complete spaghetti, while being lightweight, scaleable and portable.  

I also aim to keep this framework structured, everything has it's place, no variables or function calls in obscure random places.  

Therefore this framework will not be bundled with bloatware such as modules/components/addons/plugins or other third-party libraries, more than absolutely necessary.  
Only exception to this rule is jQuery, while it is not strictly required, or mandatory to use, it simply is less verbose than native javascript, it is safe to remove this if preffered.

In short, all this does is serve as a kickstart to get a readable and stable codebase when starting up a new custom web project.

# Documentation

## Controller & Methods
If you're familiar with MVC frameworks you might already know the url-to-controller concept.  
Given the URL **yourdomain.tld/animal** will map to a controller as such **AnimalController.php** in the application/controllers/ directory.  

Your controllers must extend upon **Controller** to have all the neccessary functions available.

Additionally the method to be called on your controller can be set by the next argument in the request URI, alias arg(1).  

**yourdomain.tld/animal/tiger** will trigger **AnimalController()->tiger();** to be called.  

arg(1) will be sanitized to a PHP5 compatible method name in camelCase format, this means that dashes, numbers, everything that's not a word will be stripped
and the upcoming word will be uppercased, except the first.  

By design there's no way for PHP to validate that you (namely the developer),
define your methods in camelCaseFormat, so please! for you and the next developers sake, do this, and be strict about it. 

Any other parts beyond arg(1) ARE NOT passed directly to the controller, these are for you to pick up using the applications arg() method.  
The **\Core\Application()->arg();** method starts from index 0, whereas the first two indices are already used by the core to determine the route.  

```php
<?php
	// Assume this url: yourdomain.tld/animals/tiger/indo-chinese
	class AnimalController extends Controller {
		public function index() {
			// Will only trigger at yourdomain.tld/animal
		}

		public function tiger() {
			var_dump($this->application->arg(2)); // indo-chinese
		}
	}
```

The above example will output something similar to:  

```
string(12) "indo-chinese"
```

Controllers may also set child controllers to be executed once the parent controller finalizes.  

```php
	class AnimalController extends Controller {
		public function index() {
			$this->children["Tiger"];
		}
	}
```
Will result in `TigerController` being invoked as if it was a normal controller, AFTER `AnimalController`

Children controllers will also be able to override any data set by the parent controller.

Setting a different view, will automatically add the new view to children controllers, to ensure that the controller for said view is executed.

```php
	class AnimalController extends Controller {
		public function index() {
			$this->setView("predator");
		}
	}
```

Will result in `PredatorController` being invoked.  

> *NOTE:*  
> The default method invoked is **index** this will happen if arg(1) is nowhere to be found in the given controller, or arg(1) is void.

## Themes folder
This is where all your theming goes (obviously du'h).  
Each theme should contain at least the following files.  

- header.tpl.php (Required)  
- footer.tpl.php (Required)  
- (default-route).tpl.php (Required) (default-route indicates a filename matching the configured default route.)  
- notfound.tpl.php (Required)  
- theme.json (Required) (This is the per-theme configurations)

It is assumed by the core that your theme has at least the required files, failing to create those files will result in unknown errors.  
  
Every view file must have the extension **.tpl.php** this is to distinguish them from their representative controller files.  
By default the view to be displayed is the one found matching arg(0), for example **animal.tpl.php**, unless otherwise specified by the dispatched controller.

You can add a new "partial" or "children" by adding it's path to the controllers data.
```php
	$this->data["sidebar"] = $this->getViewPath("sidebar");
```

And then in your template files

```php
require $sidebar;
```

Theme assets should be configured in the theme.json file, and paths must be relative to the theme directory, or an absolute url to the asset.  

> *NOTE:*  
> header.tpl.php, footer.data.php, and any other view files you plan to include or require in another view file cannot have a controller file.  

## Command Line Interface
This framework supports being queried through CLI (albeit, not fully tested), to do so you must query the **index.php** file.  

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
The main configuration resides within the file **config,json**, and should contain nothing but configuration settings used by the core and controllers.  

Theme specific configurations such as assets, third-party libraries should be managed by the **theme.json** file bundled with the theme.  

Configuration is loaded upon controller initialization.  
Values can be accessed, changed, removed and saved using a dot syntax.  
```php
	class RestaurantController extends Controller {
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
"version": "1.0.0"
"site_name": "Framework",
"http": {
    "useragent": "{{site_name}}/{{version}}"
}
```

```php
	// The following would return Framework/1.0.0
	Registry::get("Core\Configuration")->get("http.useragent");
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

> *WARNING:*  
> Calling the **Configuration()->save();** method will overwrite the current configuration file and write current configuration settings to the loaded JSON file.  

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
  
## The Registry
Is where all instances that should be globally accessible is stored.  

Once an instance has been set in the registry, it is immediately accesible by using **Registry::get()** instances are keyed by their class name definitions.  
The instance registered, will be returned.  
  
Examples:  
```php
	// Exmaple 1
	$currentUser = Registry::set( new User($uid) );

	// Example 2
	$currentUser = Registry::get("User");
```

In case an instance is namespaced the namespace should also be specified (without the initial backslash) upon retrieval.

Example:  
```php
	Registry::set( new \Alerts\Error("No more cheese for the pizza...") );

	print Registry::get("Alerts\Error")->getMessage(); // Would print: "No more cheese for the pizza"
```
  
This structure is in place to avoid singletons being misused.  
Albeit this framework currently ships with a \Singleton(); class, it's use is discouraged, as it is currently deprecated, and to be removed later on.  
  
## Database
This section assumes you have basic knowledge of PDO.  
(I haven't yet had time to properly test this documentation, as though it may appear outdated, use at own risk.)

1. **\Registry::get("Database\Connection")->query()**  

```php
\Registry::get("Database\Connection")->query("UPDATE animals SET `extinct` = :value WHERE name = :name", ["value" => true, "name" => "Asian Rhino"]);
```   

This could also be written as follows:  
```php
\Registry::get("Database\Connection")->update("animals", ["extinct" => true], ["name" => "Asian Rhino"]);
```

Queries with a return value will be fetched as objects, for instance:  
```php
\Registry::get("Database\Connection")->select("animals");
```
  
## Database Entities
For easier data manipulation, data objects should extend the **\Database\Entity** class.  
Every class that extends **\Database\DBObject** must implement the following methods.  

- getTableName(); // Table in which this data object should store data.  
- getKeyField(); // The primary key of the table in which this object stores data.  

Every data object take an optional parameter [(int) primary_key] upon instantiating,  
identifying whether a new data object should be instantiated or an already existing row should be loaded from the table.  

If you wish to change data use the **->set(array('column' => 'value'));**  
This will allow you to call **->save();** on an object and thus saving the data to your database.  
The data object will be saved as a new row if the primary_key key parameter was not present upon instantiating.  
  
## The Document class
In the DOM namespace you'll find the Document class, this can be used to add stylesheets and javscript to the page.  
Do either of the following to achieve this.  
**\DOM\Document::addStylesheet();**, **\DOM\Document::addJavascript();** methods.  
ressources are rendered in the same order they are added  
  
If you desire to add custom media stylesheets make use of the second parameter **$media** in **Document::addStylesheet();**  
Same goes for the **Document::addJavascript();** method for other regions than the footer.  

## Languages (I18n)
String translations may by enabled by setting the key **enable_i18n** to a boolean in **config.json**
When enabling internationalization it is also required to set the default language with the key **default_language**
All language keys should be a 2 character language code.

Add/enable other languages by creating a **langcode**.json file in the **language/** folder
The default language does not require a language file, as the original string will just be returned when calling translation functions.

Current/default language will automatically be prepended to the request uri, and a redirect will be performed.

If a user tries to access a non-configured language, an 404 page will be served