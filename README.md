# Allan Rehhoff's Framework #

This is not what you'd typically associate with a fully functional MVC framework, and that is intentional.  
The intention for this is to prevent the developer from writing complete spaghetti, while being leightweight and scaleable.  

Therefore this framework will not be bloated with modules and libraries more than absolutely necessary.  

In short all this does is serve as a kickstart to get a good and readable code base when starting up a new  
custom web project.

#Documentation#

##Themes##
This is where all your themes goes (obviously).  

Each theme has 5 core files  
- header.tpl.php (Required)  
- footer.tpl.php (Required)  
- index.tpl.php (Required)  
- 404.tpl.php (Required)  
- functions.php (Optional)  
 
It is assumed that your theme has at least the required files, everything else is considered an *optional file*

Every theme file must have the extension *.tpl.php* this is to distinguish them from their representative controller files.  
Every optional theme file can have a possible controller matching the theme filename, while ending on the .php extension

The optional file *functions.php* is special, this file should be used to contain all custom functions used by your theme, and cannot have a controller file.    

> *NOTE:*
> header.tpl.php, footer.tpl.php, and other template files you plan to include in your optional theme files cannot have a controller file.


##Permalinks##
Given the URL *yourdomain.tld/animals* will map to optional theme and controller files. If no matching files is found, a 404 page will be issued.  
Every other parameter after that, is the *animals.tpl.php* files responsibility to handle properly.  
You can get those arguments with $app->arg(index); failing to provide an index will give you the whole array of arguments.  

The public folder is where you should keep your static content such as stylesheets, javascript and images. 

Any links in your theme files should be passed through Helper::url() like so: Helper::url('/path/to/stylesheet.css'); To ensure that the file is being linked correct. (In most cases if the application resides in a subfolder)  

##Configuration##
The configuration resides within the file config,json, and should contain nothing but configuration settings used by the application.  
Configuration is loaded upon initialization, values can be accessed and changed using a dot syntax. for instance $app->config->get('database.name');  

Calling the ->save(); method will overwrite the current configuration file with current application settings.

The base_title setting only supports one wildcard %s use ->setTitle($title) to set a dynamic title  