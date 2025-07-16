# Errors and Exceptions
The application comes bundled with rather conservative error/exception handlers, the handlers are very aggresive and will take care of generating a small stacktrace for debugging purposes.  
Every PHP notice/error will be treated as fatal and an exception will be thrown.

This is to prevent the next developer from banging his head into a table later on, as those errors should be dealt with during development.  

Good practice dictates that while developing your custom classes you should also create custom exceptions in the same namespace/domain to match your classes.  

Errors will by default be logged to `storage/logs/php_errors.log`.  
Combine this with `tail -f` for real-time error reporting.

> [!NOTE]
> It is recommended to set up a cronjob or service that either rotates logfiles or truncates large logfiles regularly,
> to prevent disk space issues and keep logs manageable. Tools like `logrotate` or a simple `truncate` command can be used for this purpose.
