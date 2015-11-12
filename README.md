#Allan Rehhoff's Framework#

##Introduction##
This is not what you'd typically associate with a fully functional MVC framework, and that is intentional.  
The intention for this is to prevent the developer from writing complete spaghetti, while being leightweight, scaleable and portable.  

Therefore this framework will not be bundled with bloat like modules and libraries more than absolutely necessary.  

In short all this does is serve as a kickstart to get a readable and stable code base when starting up a new custom web project.

#Documentation#

##Themes and controllers##
This is where all your themes goes (obviously).  

Each theme should contain at least the following files.  

- header.tpl.php (Required)  
- footer.tpl.php (Required)  
- index.tpl.php (Required)  
- 404.tpl.php (Required)  
- functions.php (Optional)  

It is assumed by the core that your theme has at least the required files.  

The optional file **functions.php** is somewhat special, this file should be used to contain all custom functions used by your theme, and cannot have a controller file.  
Every other file is considered a view file.  
While **functions.php** is an optional file, it is highly recommended that you make use of it.  
  
Every view file must have the extension **.tpl.php** this is to distinguish them from their representative controller files.  

Any view file can have a possible controller matching the base filename, while ending on the .php extension.  
Additionally any controller is not required to have a matching view file, this behaviour was intended for ajax requests.  

> *NOTE:*  
> header.tpl.php, footer.data.php, and any other view files you plan to include or require in another view file cannot have a controller file.  
  
Stylesheets and javascript for your theme should be placed in either /public/* or in your theme folder, depending on your use for that particular file.  
However you should consider consulting other develoeprs on the project about your application structure on this matter.  

##Permalinks##
Given the URL **yourdomain.tld/animals** will map to optional theme and controller files. If no matching files is found, a 404 page will be issued.  
Every other parameter after that, is the **animals.tpl.php** files responsibility to handle properly.  
You can get those arguments with $app->arg(index); failing to provide an index will give you the whole array of arguments.  

The public folder is where you should keep your static content such as stylesheets, javascript and images.

Any links in your theme files should be passed through **Helper::url()** like so: **Helper::url('/path/to/stylesheet.css');** To ensure that the file is being linked correct. (In most cases if the application resides in a subfolder)  

##Configuration##
The configuration resides within the file **config,json**, and should contain nothing but configuration settings used by the application.  
Configuration is loaded upon initialization, values can be accessed and changed using a dot syntax. for instance **$app->config->get('database.name');**  
You can dynamically change a configuration setting using the **->set('database.username', 'root')**  
Calling the **->save();** method will overwrite the current configuration file and write current configuration settings to **config.json**  

The base_title setting only supports one wildcard %s use **->setTitle($title)** in your controller files to set a dynamic title.  

> *NOTE:*  
> Configuration can be accessed in every scope as such \Core\ConfigurationParser::getInstance();  
> This is intended for use inside scopes such as functions and classes.  

##Database##
This section assumes you have basic knowledge of PDO.  
Database queries can be executed through **$app->db->query();**  
For instance you could do:   

```
<?php $app->db->query("UPDATE animals SET `extinct` = :value WHERE name = :name", ["extinct" => true, "name" => "Asian Rhino"]); ?>
```   

> *NOTE:*  
> Database can be accessed in every scope as such \Database\DbConnection::getInstance();  
> This is intended for use inside scopes such as functions and classes.  

##Data Objects##
For easier data manipulation, data objects should extend the **Core\DBObject** class.  
Every class that extends **\Core\DBObject** must implement the following methods.  

- getTableName(); // Table in which this data object should store data.  
- getKeyField(); // The primary key of the table in which this object stores data.  

Every data object take an optional parameter [(int) primary_key] upon instantiating,  
identifying whether a new data object should be instantiated or an already existing row should be loaded from the table.  

if you wish to change data use the **->set(array('column' => 'value'));**  
This will allow you to call **->save();** on an object and thus saving the data to your database.  
The data object will be saved as a new row if the primary_key key parameter was not present upon instantiating.  

##The Document class##
In the DOM namespace you'll find the Document class, this can be used to add stylesheets and javscript to the page.  
Do either of the following to achieve this.  
**DOM\Document::addStylesheet();**, **DOM\Document::addJavascript();** methods.  
ressources are rendered in the same order they are added  
  
If you desire to add custom media stylesheets make use of the second parameter **$media** in **Document::addStylesheet();**  
Same goes for the **Document::addJavascript();** method for other regions than the footer.  

> *NOTE:*  
> You must manually implement rendering of custom media stylesheets and custom region javascripts. as only the defaults will be rendered by the core files.  
  
##Errors and Exceptions##
The application comes bundled by default with an error handler and exception handler, those handlers will take care of generating a small stacktrace for debugging purposes.  
Every PHP error is treated as a fatal error by the error handler, this is to prevent the next developer from banging his head into the table later on, as those errors should be dealt with during development.  
  
However if you do decide to annoy the next developer you can turn of error reporting entirely by using the **ini_* ** functions in **preprocess.php**

The exception handler will still kill your application however, due exceptions being thrown around.  

While developing your custom classes you should also create custom exceptions in the same namespace to match your classes.