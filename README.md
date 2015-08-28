#Allan Rehhoff's Framework#

##Introduction##
This is not what you'd typically associate with a fully functional MVC framework, and that is intentional.  
The intention for this is to prevent the developer from writing complete spaghetti, while being leightweight and scaleable.  

Therefore this framework will not be bloated with modules and libraries more than absolutely necessary.  

In short all this does is serve as a kickstart to get a good and readable code base when starting up a new custom web project.

#Documentation#

##Themes##
This is where all your themes goes (obviously).  

Each theme should contain of at least the following files.  
* header.tpl.php (Required)  
* footer.tpl.php (Required)  
* index.tpl.php (Required)  
* 404.tpl.php (Required)  
* functions.php (Optional)  

It is assumed by the core that your theme has at least the required files, everything else is considered an *optional file*  

Every theme file must have the extension **.tpl.php** this is to distinguish them from their representative controller files.  
Every optional theme file can have a possible controller matching the theme filename, while ending on the .php extension

The optional file **functions.php** is somewhat special, this file should be used to contain all custom functions used by your theme, and cannot have a controller file.

> *NOTE:*
> header.tpl.php, footer.tpl.php, and any other template files you plan to include or require in optional theme files cannot have a controller file.


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
Calling the **->save();** method will overwrite the current configuration file with current application settings.

The base_title setting only supports one wildcard %s use **->setTitle($title)** in your controller files to set a dynamic title.  

##Data Objects##
For easier data manipulation data objects should extend the **Core\DBObject** class.  
Every class that extends **Core\DBObject** must implement the following methods.  
* getTableName(); // Table in which this data object should store data.  
* getKeyField(); // The primary key of the table in which this object stores data.  

Every data object take an optional parameter [(int) primary_key] upon instantiating,  
identifying whether a new data object should be instantiated or an already existing row should be loaded from the table.  

if you wish to change data use the **->set(array('column' => 'value'));**  
This will allow you to call **->save();** on an object and thus saving the data to your database.  
The data object will be saved as a new row if the primary_key key parameter was not present upon instantiating.  
