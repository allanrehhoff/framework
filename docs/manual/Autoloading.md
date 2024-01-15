# Autoloading classes
Autoloading is a mechanism which requires class, interface and trait definitions (from here on, referenced as instances) on demand.  
Files containing the definition of a class must share name with the class name, and have the extention `.php`.  
Additionally instances residing within a namespace must be located within a folder structure matching the the namespacing structure (relative from classes/ folder).  

Controller files are autoloaded from the `src/Controllers` directory.  

Other classes are autoloaded from the `src/Libraries` directory.  

The `composer` generated autoloader will be registered, when the `vendor` directory is present in either document root, or its parent.