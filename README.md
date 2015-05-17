# Allan Rehhoff's Framework #

This is not what you'd typically associate with a fully functional MVC framework, and that is intentional.  
The intention is for this to be super lightweight and easy to use, while being scalable.  

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
Every optional theme file can have a possible controller named by *animals.php*  

The optional file *functions.php* is special, this file should be used to contain all custom functions used by your theme, and cannot have a controller file.    
  
header.tpl.php, footer.tpl.php, and other template files you plan to include in your optional theme files cannot have a controller file.
  

##Permalinks##
Given the URL *yourdomain.tld/animals* will map to optional theme and controller files. If no matching files is found, a 404 page will be issued.  
Every other parameter after that, is the *animals.tpl.php* files responsibility to handle properly.

The public folder is where you should keep your static content such as stylesheets, javascript and images. 