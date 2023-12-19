# Logging, Timing and debug

The `\Logger` class provides simple methods to:

* Keep track of log entries.
* Keep track of timings with `time()` and `timeEnd()` methods, Javascript style.
* Optionally write log entries in real-time to file or to screen.
* Optionally dump the log to file in one go at any time.

Log entries can be added with any of the following methods:

* `\Logger::debug($message, $name = '')` > a diagnostic message intended for the developer.
* `\Logger::info($message, $name = '')`  > an informational message intended for the user.
* `\Logger::warning($message, $name = '')` > a warning that something might go wrong.
* `\Logger::error($message, $name = '')` > explain why the program is going to crash.

The `$name` argument is optional; if present, it will be prepended to the message: "$name => $message".  

# Examples

> [!WARNING]
> The class uses static methods and internal flags (e.g. `$loggerReady`) to keep its state.  
> This is done to make the class work straight away, without any previous configuration or the need to instantiate it.  
> But can potentially create race conditions if you are running processes in parallel.  

The following code:

```php
\Logger::$printLog = true;
\Logger::$logLevel = "debug";

\Logger::debug("variable x is false");
\Logger::info("program started");
\Logger::warning("variable not set, something bad might happen");
\Logger::error("file not found, exiting");
```

will print something similar to the following, to STDOUT:

```
$> 2021-07-21T11:11:03+02:00 [DEBUG] : variable x is false
$> 2021-07-21T11:11:03+02:00 [INFO] : program started
$> 2021-07-21T11:11:03+02:00 [WARNING] : variable not set, something bad might happen
$> 2021-07-21T11:11:03+02:00 [ERROR] : file not found, exiting
```

Write to a log file:  
```php
\Logger::$writeLog = true;
\Logger::$logLevel = "debug";

\Logger::$logDir = "storage/logs";
\Logger::$logFileName = "application";
\Logger::$logFileExtension = "log";

\Logger::debug("variable x is false");
\Logger::info("program started");
\Logger::warning("variable not set, something bad might happen");
\Logger::error("file not found, exiting");
```

will append something similar to the following to `storage/logs/application.log`;

```
$> 2021-07-21T11:11:03+02:00 [DEBUG] : variable x is false
$> 2021-07-21T11:11:03+02:00 [INFO] : program started
$> 2021-07-21T11:11:03+02:00 [WARNING] : variable not set, something bad might happen
$> 2021-07-21T11:11:03+02:00 [ERROR] : file not found, exiting
```

# Timing

You can keep track of elapsed time by using the `\Logger::time()` and `\Logger::timeEnd()` function.

```php
\Logger::time();
sleep(1);
\Logger::timeEnd();
```

Will print something similar to:

```
$> 2022-04-19T17:26:26+00:00 [DEBUG] : Elapsed time => 1.003163 seconds
```

### Named timers

If you need to time different processes at the same time, you can use named timers.

```php
\Logger::time("outer timer");
sleep(1);

	\Logger::time("inner timer");
	sleep(1);

	\Logger::timeEnd("inner timer");

\Logger::timeEnd("outer timer");
```

will print something similar to:

```
$> 2022-04-19T17:32:15+00:00 [DEBUG] : Elapsed time for 'inner timer' => 1.002268 seconds
$> 2022-04-19T17:32:15+00:00 [DEBUG] : Elapsed time for 'outer timer' => 2.006117 seconds
```

# Options

To customize the logger, you can either:

- extend the class and override the static properties or
- set the static properties at runtime.

In the following examples, we adopt the second approach.

## Set the log level

By default, the logger will assume it runs in production and, therefore, will print only error-level messages.

Specify your desired log level in the following way:

```php
\Logger::$logLevel = "error"; // Show only errors
\Logger::$logLevel = "warning"; // Show warnings and errors
\Logger::$logLevel = "info"; // Show info messages, warnings and errors
\Logger::$logLevel = "debug"; // Show debug messages, info messages, warnings and errors
```

## Prevent printing to stream
To prevent printing to STDOUT:

```php
\Logger::$printLog = false;
```

## Write to file

To also write to file, set:

```php
\Logger::$writeLog = true;
```

To customize the log file path:

```php
\Logger::$logDir = "storage/logs";
\Logger::$logFileName = "application";
\Logger::$logFileExtension = "log";
```

To overwrite the log file at every run of the script:

```php
\Logger::$logFileAppend = false;
```
