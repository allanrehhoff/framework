# Errors and Exceptions
The application comes bundled with rather conservative error/exception handlers, the handlers are very aggresive and will take care of generating a small stacktrace for debugging purposes.  
Every PHP notice/error will be treated as fatal and an exception will be thrown.

This is to prevent the next developer from banging his head into a table later on, as those errors should be dealt with during development.    
However if you do wish to annoy the next developer you can turn of error reporting entirely by using the PHP built-in `ini_*` functions in `startup.php`

Good practice dictates that while developing your custom classes you should also create custom exceptions in the same namespace to match your classes.  