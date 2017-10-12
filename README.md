#Introduction#
This is not what you'd typically associate with a fully functional MVC framework, there's no "Models" and that is intentional, deal with it.  
The intention for this is to prevent the developer from writing complete spaghetti, while being lightweight, scaleable and portable.  

I also aim to keep this framework structured, everything has it's place, no variables or function calls in obscure random places.  

Therefore this framework will not be bundled with bloatware such as modules/components/addons/plugins and third-party libraries, more than absolutely necessary.  

In short all this does is serve as a kickstart to get a readable and stable codebase when starting up a new custom web project.

#Documentation#

##Controllers##
If you're familiar with opencart or other MVC frameworks you might already know the url-to-controller concept.  
Given the URL **yourdomain.tld/animals** will map to a controller as such **AnimalController.php** in the application/controllers/ directory.  

Your controllers must extend upon **\Core\Controller** to have all the neccessary functions available.

Additionally the method to be called in your controller can be set by the next argument in the request URI.

**yourdomain.tld/animals/tiger** will trigger **AnimalController()->tiger();** to be called.  
Any other parts ARE NOT passed to the method, these are for you to pick up using the applications arg() method.  
The **\Core\Application()->arg();** method starts from index 0, whereas the first two indices are already used by the core to determine the route.  

```
<?php
	// Assume this url: yourdomain.tld/animals/tiger/indo-chinese
	class AnimalController extends \Core\Controller {
		public function index() {}

		public function tiger() {
			var_dump($this->application->arg(2));
		}
	}
?>
```

The above example will output something similar to:  

```
string(12) "indo-chinese"
```

> *NOTE:*  
> The default method to be called is "index" if arg(1) is nowhere to be found.  

##Views##
This is where all your theming goes (obviously du'h).  

Each theme should contain at least the following files.  

- header.tpl.php (Required)  
- footer.tpl.php (Required)  
- (default-route).tpl.php (Required) (default-route indicates a filename matching the configured default route.)  
- notfound.tpl.php (Required)  
- theme.json (Required)

It is assumed by the core that your theme has at least the required files, failing to create those files will result in unknown errors.  
  
Every view file must have the extension **.tpl.php** this is to distinguish them from their representative controller files.  
By default the view to be displayed is the one found matching arg(0), for example **animal.tpl.php**, unless otherwise specified by the active controller.

You can add a new "partial" or "children" by adding it's path to the controllers data.
```
<?php
	$this->data["sidebar"] = $this->getViewPath("sidebar");
?>
```

> *NOTE:*  
> header.tpl.php, footer.data.php, and any other view files you plan to include or require in another view file cannot have a controller file.  
  
##Configuration##
The main configuration resides within the file **config,json**, and should contain nothing but configuration settings used by the core and controllers.  

Theme specific configurations such as assets, third-party libraries should be managed by the **theme.json** file bundled with the theme.  

Configuration is loaded upon controller initialization.  
Values can be accessed, changed, removed and saved using a dot syntax.  
```
<?php
	class RestaurantController extends \Core\Controller {
		public function index() {
			$this->theme->get("menu.pizzas"); // ["Hawaii", "MeatLover", "Vegan", ...]
			$this->theme->set("menu.pizzas.Hawaii", "Ananas"); // ["Ananas", "MeatLover", "Vegan", ...]
			$this->theme->remove("menu.pizzass.Vegan"); // ["Ananas", "MeatLover", ...]
			$this->theme->save();
		}
	}
?>
```

> *WARNING:*  
> Calling the **Configuration()->save();** method will overwrite the current configuration file and write current configuration settings to the loaded JSON file.  

The core base_title setting only supports one wildcard %s use **(controller)->setTitle($title)** in your controller files to set a dynamic title.  
  
##Autoloading classes##
Autoloading is a mechanism which requires class, interface and trait definitions (from here on, referenced as instances) on demand.  
Files containing the definition of an instance must share name with the instance name, and end on **.class.php**.  
Additionally instances residing within a namespace must be located within a folder structure matching the the namespacing structure (relative from classes/ folder).  
  
##Errors and Exceptions##
The application comes bundled by default with a rather agressive error and exception handler, those handlers will take care of generating a small stacktrace for debugging purposes.  
Every PHP notice/error is treated as a fatal error by the error handler, this is to prevent the next developer from banging his head into the table later on, as those errors should be dealt with during development.  
  
However if you do decide to be a nincompoop and annoy the next developer you can turn of error reporting entirely by using the **ini_* ** functions in **preprocess.php**

The exception handler will still kill your application however, due to exceptions being thrown around.  

Good practice dictates that while developing your custom classes you should also create custom exceptions in the same namespace to match your classes.  
  
##The Registry##
Is where all instances that should be globally accessible is stored.  

Once an instance has been set in the registry, it is immediately accesible by using **Registry::get()** instances are keyed by their class name definitions.  
The instance registered, will be returned.  
  
Examples:  
```
<?php
	// Exmaple 1
	$currentUser = Registry::set( new User($uid) );

	// Example 2
	$currentUser = Registry::get("User");
?>
```

In case an instance is namespaced the namespace should also be specified (without the initial backslash) upon retrieval.

Example:  
```
<?php
	Registry::set( new \Alerts\Error("No more cheese for the pizza...") );

	print Registry::get("Alerts\Error")->getMessage(); // Would print: "No more cheese for the pizza"
?>
```
  
This structure is in place to avoid singletons being misused.  
Albeit this framework currently ships with a \Singleton(); class, it's use is discouraged, as it is currently deprecated, and to be removed later on.  
  
##Database##
This section assumes you have basic knowledge of PDO.  
(I haven't yet had time to properly test this documentation, as though it may appear outdated, use at own risk.)

1. **\Registry::get("Database\Connection")->query()**  

```
<?php \Registry::get("Database\Connection")->query("UPDATE animals SET `extinct` = :value WHERE name = :name", ["value" => true, "name" => "Asian Rhino"]); ?>
```   

This could also be written as follows:  
```
<?php \Registry::get("Database\Connection")->update("animals", ["extinct" => true], ["name" => "Asian Rhino"]); ?>
```

Queries with a return value will be fetched as objects, for instance:  
```
<?php \Registry::get("Database\Connection")->select("animals"); ?>
```
  
##Database Entities##
For easier data manipulation, data objects should extend the **\Database\Entity** class.  
Every class that extends **\Database\DBObject** must implement the following methods.  

- getTableName(); // Table in which this data object should store data.  
- getKeyField(); // The primary key of the table in which this object stores data.  

Every data object take an optional parameter [(int) primary_key] upon instantiating,  
identifying whether a new data object should be instantiated or an already existing row should be loaded from the table.  

If you wish to change data use the **->set(array('column' => 'value'));**  
This will allow you to call **->save();** on an object and thus saving the data to your database.  
The data object will be saved as a new row if the primary_key key parameter was not present upon instantiating.  
  
##The Document class##
In the DOM namespace you'll find the Document class, this can be used to add stylesheets and javscript to the page.  
Do either of the following to achieve this.  
**\DOM\Document::addStylesheet();**, **\DOM\Document::addJavascript();** methods.  
ressources are rendered in the same order they are added  
  
If you desire to add custom media stylesheets make use of the second parameter **$media** in **Document::addStylesheet();**  
Same goes for the **Document::addJavascript();** method for other regions than the footer.  

The document class also takes care of displaying the page title in **header.tpl.php** using the method **->getTitle()**    
Set the page title with the **->setTitle()** method  

> *NOTE:*  
> You must manually implement rendering of custom media stylesheets and custom region javascripts. as only the defaults will be rendered by the core files.  
  
##Permalinks##
Any links in your theme files should be passed through **Functions::url()** like so: 
```
<?php Functions::url("/path/to/your-file.ext") ?>
```
This ensures the file is being linked correct, in most cases if the application is installed in a subfolder.  
You may also link the full path manually, the above is solely a helper method.  

However, **do not** link assets this way, use the theme configuration for this.  