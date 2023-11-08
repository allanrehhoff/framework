## Command Line Interface
This framework supports being queried through CLI, to do so you must query the `index.php` file with `php index.php`.  
Or use the bundled `bin/app` file, remember to add execution permissions to this file, should you decide to use it.  

You may safely rename the file to any abbreviation matching your project.  
Additionally you may also move/copy the fil to any directory in your `$PATH` for easier execution.  

```
$ bin/app controller method [argument] ...
```
or
```
$ chmod +x bin/app
$ bin/app controller method [argument] ...
```
  
Just as the URL scheme, the first argument maps to the controller being used, second the method, and any other arguments is for the method to handle.  

Assume `CliController` in the following example.  

```
$ bin/app cli
Hello from cli

$ bin/app cli interface
Hello from interface
```